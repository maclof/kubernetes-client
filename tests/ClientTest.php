<?php

use Maclof\Kubernetes\Client;
use Maclof\Kubernetes\Collections\PodCollection;
use Maclof\Kubernetes\Collections\ReplicationControllerCollection;
use Maclof\Kubernetes\Collections\ServiceCollection;
use Maclof\Kubernetes\Models\Pod;
use Maclof\Kubernetes\Models\ReplicationController;
use Maclof\Kubernetes\Models\Service;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

function dd()
{
	die(var_dump(func_get_args()));
}

class ClientTest extends TestCase
{
	protected function getClient(GuzzleClient $guzzleClient = null)
	{
		return new Client([
			'master' => '',
		],
		$guzzleClient);
	}

	protected function getMockGuzzleCLient(RequestInterface $guzzleRequest = null, ResponseInterface $guzzleResponse = null)
	{
		$mockGuzzleClient = Mockery::mock(GuzzleClient::class);

		if ($guzzleRequest && $guzzleResponse) {
			$mockGuzzleClient->shouldReceive('send')->with($guzzleRequest)->andReturn($guzzleResponse);
		}

		return $mockGuzzleClient;
	}

	protected function getMockGuzzleRequest()
	{
		$mockGuzzleRequest = Mockery::mock(RequestInterface::class);

		return $mockGuzzleRequest;
	}

	protected function getMockGuzzleResponse(array $response = array())
	{
		$mockGuzzleResponse = Mockery::mock(ResponseInterface::class);
		$mockGuzzleResponse->shouldReceive('json')->with()->andReturn($response);

		return $mockGuzzleResponse;
	}

	public function test_get_guzzle_client()
	{
		$client = $this->getClient();

		$this->assertInstanceOf(GuzzleClient::class, $client->getGuzzleClient());
	}

	public function test_get_pods()
	{
		$request = $this->getMockGuzzleRequest();
		$response = $this->getMockGuzzleResponse([
			'items' => [
				[],
				[],
				[],
			],
		]);
		$mockGuzzleClient = $this->getMockGuzzleCLient($request, $response);
		$mockGuzzleClient->shouldReceive('createRequest')
			->with('GET', '/api/' . $this->apiVersion . '/namespaces/' . $this->namespace . '/pods', ['body' => null])
			->andReturn($request);

		$client = $this->getClient($mockGuzzleClient);
		$pods = $client->getPods();

		$this->assertInstanceOf(PodCollection::class, $pods);
	}

	public function test_create_pod()
	{
		$this->assertTrue(true);
	}
}