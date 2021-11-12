<?php

use Http\Client\Common\HttpMethodsClient;
use Maclof\Kubernetes\Client;
use Maclof\Kubernetes\Collections\PodCollection;
use Maclof\Kubernetes\Models\Pod;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;

class ClientTest extends TestCase
{
	public function testSendRequestJsonParsesResponse()
	{
		$httpClientProp = new ReflectionProperty(Client::class, 'httpClient');
		$httpClientProp->setAccessible(true);
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
	) {
		$httpClientProp = new ReflectionProperty(Client::class, 'httpClient');
		$httpClientProp->setAccessible(true);

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

	public function testGetPodsFromApi()
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

	public function providerForFailedResponses()
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
	public function testExceptionIsThrownOnFailureResponse(int $respCode, string $exceptionClass, string $msgRegEx)
	{
		$client = new Client();

		$this->setMockHttpResponse(
			$client,
			['message' => 'Error hath occurred'],
			["GET", "/api/v1/namespaces/default/api/anything", [], null],
			$respCode
		);

		$this->expectException($exceptionClass);
		$this->expectExceptionMessageRegExp($msgRegEx);
		$client->sendRequest('GET', '/api/anything');
	}
}
