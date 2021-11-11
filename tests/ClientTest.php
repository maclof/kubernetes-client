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

	private function setMockHttpResponse(
		Client $client,
		array $mockResponse,
		array $expectedSendArgs,
		int $respStatusCode = 200,
		array $respHeaders = []
	) {
		$httpClientProp = new ReflectionProperty(Client::class, 'httpClient');
		$httpClientProp->setAccessible(true);

		$mockHttpMethodsClient = $this->getMockBuilder(HttpMethodsMockClient::class)
			->setMethods(['send'])
			->getMock();

		$jsonBody = json_encode($mockResponse);

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
}
