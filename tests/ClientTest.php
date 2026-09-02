<?php

use Http\Client\Common\HttpMethodsClient;
use Http\Client\Exception\HttpException;
use Http\Client\Exception\RequestException;
use Maclof\Kubernetes\Client;
use Maclof\Kubernetes\Collections\PodCollection;
use Maclof\Kubernetes\Exceptions\BadRequestException;
use Maclof\Kubernetes\Models\Pod;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;

class ClientTest extends TestCase
{
	public function testKubeconfigWithoutCaKeepsTlsVerificationEnabled(): void
	{
		$options = Client::parseKubeconfig([
			'current-context' => 'test',
			'contexts' => [['name' => 'test', 'context' => ['cluster' => 'test', 'user' => 'test']]],
			'clusters' => [['name' => 'test', 'cluster' => ['server' => 'https://api.example.com']]],
			'users' => [['name' => 'test', 'user' => []]],
		], 'array');

		$this->assertTrue($options['verify']);
	}

	public function testKubeconfigCanExplicitlyDisableTlsVerification(): void
	{
		$options = Client::parseKubeconfig([
			'current-context' => 'test',
			'contexts' => [['name' => 'test', 'context' => ['cluster' => 'test', 'user' => 'test']]],
			'clusters' => [[
				'name' => 'test',
				'cluster' => [
					'server' => 'https://api.example.com',
					'insecure-skip-tls-verify' => true,
				],
			]],
			'users' => [['name' => 'test', 'user' => []]],
		], 'array');

		$this->assertFalse($options['verify']);
	}

	/**
	 * @dataProvider healthAndVersionProvider
	 */
	public function testRootEndpointUrlHasSingleSlash(string $method, string $path, string $responseBody): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->with($this->callback(function ($request) use ($path): bool {
				$this->assertSame('https://api.example.com/' . $path, (string) $request->getUri());

				return true;
			}))
			->willReturn(new Response(200, [], $responseBody));
		$client = new Client(['master' => 'https://api.example.com'], null, $httpClient);

		$client->{$method}();
	}

	public function healthAndVersionProvider(): array
	{
		return [
			['health', 'healthz', 'ok'],
			['version', 'version', '{}'],
		];
	}

	public function testSendRequestJsonParsesResponse(): void
	{
		$httpClientProp = new ReflectionProperty(Client::class, 'httpClient');
		if (PHP_VERSION_ID < 80100) {
			$httpClientProp->setAccessible(true);
		}
		$client = new Client([
			'master' => 'https://api.example.com',
		]);

		$mockClientInterface = $this->getMockBuilder(ClientInterface::class)
			->setMethods(['sendRequest'])
			->getMock();

		$jsonBody = json_encode([
			'message' => 'Hello world',
		]);

		$response = new Response(200, [], $jsonBody);

		$mockClientInterface->expects($this->once())
			->method('sendRequest')
			->withAnyParameters()
			->willReturn($response);

		$httpClient = new HttpMethodsClient($mockClientInterface, new Psr17Factory());

		$httpClientProp->setValue($client, $httpClient);

		$result = $client->sendRequest('GET', '/v1/poddy/');

		$this->assertSame([
			'message' => 'Hello world',
		], $result);
	}

	public function testRequestExceptionWithoutResponseIsRethrown(): void
	{
		$factory = new Psr17Factory();
		$request = $factory->createRequest('GET', 'https://api.example.com/api/v1/namespaces/default/pods');
		$exception = new RequestException('TLS certificate verification failed', $request);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->willThrowException($exception);

		$client = new Client(
			['master' => 'https://api.example.com'],
			null,
			$httpClient
		);

		$this->expectExceptionObject($exception);

		$client->sendRequest('GET', '/pods');
	}

	public function testHttpExceptionWithResponseIsConvertedToBadRequestException(): void
	{
		$factory = new Psr17Factory();
		$request = $factory->createRequest('GET', 'https://api.example.com/api/v1/namespaces/default/pods');
		$responseBody = '{"kind":"Status","apiVersion":"v1","status":"Failure","message":"invalid request","reason":"BadRequest","code":400}';
		$response = new Response(400, ['Content-Type' => 'application/json'], $responseBody);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->willThrowException(HttpException::create($request, $response));

		$client = new Client(
			['master' => 'https://api.example.com'],
			null,
			$httpClient
		);

		$this->expectException(BadRequestException::class);
		$this->expectExceptionMessage($responseBody);

		$client->sendRequest('GET', '/pods');
	}

	public function testFourHundredResponseIsConvertedToBadRequestException(): void
	{
		$responseBody = '{"kind":"Status","status":"Failure","message":"not found","reason":"NotFound","code":404}';
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->willReturn(new Response(404, ['Content-Type' => 'application/json'], $responseBody));

		$client = new Client(['master' => 'https://api.example.com'], null, $httpClient);

		$this->expectException(BadRequestException::class);
		$this->expectExceptionMessage($responseBody);

		$client->sendRequest('GET', '/missing');
	}

	public function testPodLogsPreserveJsonOutputAsText(): void
	{
		$factory = new Psr17Factory();
		$logOutput = '{"name":"job","lines":222,"jobDate":"2023-11-14 07:36:29"}';

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->willReturn(new Response(200, ['Content-Type' => 'application/json'], $logOutput));

		$client = new Client(
			['master' => 'https://api.example.com'],
			null,
			$httpClient
		);
		$pod = new Pod(['metadata' => ['name' => 'job-pod']]);

		$this->assertSame($logOutput, $client->pods()->logs($pod));
	}

	public function testJsonRequestIncludesBodyFramingAndContentType(): void
	{
		$payload = ['metadata' => ['name' => 'example']];
		$encodedPayload = json_encode($payload, JSON_FORCE_OBJECT);
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->with($this->callback(function ($request) use ($encodedPayload): bool {
				$this->assertSame($encodedPayload, (string) $request->getBody());
				$this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
				$this->assertSame((string) strlen($encodedPayload), $request->getHeaderLine('Content-Length'));

				return true;
			}))
			->willReturn(new Response(200, [], '{}'));

		$client = new Client(
			['master' => 'https://api.example.com'],
			null,
			$httpClient
		);

		$client->sendRequest('POST', '/namespaces', [], $payload, false);
	}

	/**
	 * Helper function for tests. Pass in a valid Client and have a fake response set on it.
	 * @param Client $client
	 * @param array $mockResponseData Response body (will be JSON encoded)
	 * @param array $expectedSendArgs Expected arguments of ->send() method
	 * @param int $respStatusCode Response status code
	 * @param array $respHeaders Response headers (key => value map)
	 */
	private function setMockHttpResponse(
		Client $client,
		array  $mockResponseData,
		array  $expectedSendArgs,
		int    $respStatusCode = 200,
		array  $respHeaders = []
	): void {
		$httpClientProp = new ReflectionProperty(Client::class, 'httpClient');
		if (PHP_VERSION_ID < 80100) {
			$httpClientProp->setAccessible(true);
		}

		$mockHttpMethodsClient = $this->getMockBuilder(HttpMethodsMockClient::class)
			->setMethods(['send'])
			->getMock();

		$jsonBody = json_encode($mockResponseData);

		$response = new Response($respStatusCode, $respHeaders, $jsonBody);

		$mockHttpMethodsClient->expects($this->once())
			->method('send')
			->with(...$expectedSendArgs)
			->willReturn($response);

		$httpClientProp->setValue($client, $mockHttpMethodsClient);
	}

	public function testGetPodsFromApi(): void
	{
		$client = new Client();

		$jsonBody = [
			'items' => [
				[],
				[],
				[],
			],
		];

		$this->setMockHttpResponse($client, $jsonBody, ['GET', '/api/' . $this->apiVersion . '/namespaces/' . $this->namespace . '/pods']);

		$result = $client->pods()->find();

		$this->assertInstanceOf(PodCollection::class, $result);

		$this->assertSame(3, $result->count());

		$pod1 = $result->first();
		$this->assertInstanceOf(Pod::class, $pod1);
	}

	public function providerForFailedResponses(): array
	{
		return [
			[
				500,
				\Maclof\Kubernetes\Exceptions\ApiServerException::class,
				'/500 Error/',
			],
			[
				401,
				\Maclof\Kubernetes\Exceptions\ApiServerException::class,
				'/Authentication Exception/',
			],
			[
				403,
				\Maclof\Kubernetes\Exceptions\ApiServerException::class,
				'/Authentication Exception/',
			],
		];
	}

	/**
	 * @dataProvider providerForFailedResponses
	 */
	public function testExceptionIsThrownOnFailureResponse(int $respCode, string $exceptionClass, string $msgRegEx): void
	{
		$client = new Client();

		$this->setMockHttpResponse(
			$client,
			['message' => 'Error hath occurred'],
			["GET", "/api/v1/namespaces/default/api/anything", [], null],
			$respCode
		);

		$this->expectException($exceptionClass);
		if (method_exists($this, 'expectExceptionMessageRegExp')) {
			$this->expectExceptionMessageRegExp($msgRegEx);
		} else {
			$this->expectExceptionMessageMatches($msgRegEx);
		}
		$client->sendRequest('GET', '/api/anything');
	}
}
