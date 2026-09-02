<?php

use Maclof\Kubernetes\Client;
use Maclof\Kubernetes\Models\DynamicResource;
use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;

class DynamicResourceTest extends TestCase
{
	public function testDiscoversAndListsNamespacedResource(): void
	{
		$responses = [
			new Response(200, [], json_encode([
				'groupVersion' => 'apps/v1',
				'resources' => [[
					'name' => 'statefulsets',
					'kind' => 'StatefulSet',
					'namespaced' => true,
					'verbs' => ['get', 'list'],
				]],
			])),
			new Response(200, [], json_encode([
				'kind' => 'StatefulSetList',
				'items' => [[
					'apiVersion' => 'apps/v1',
					'kind' => 'StatefulSet',
					'metadata' => ['name' => 'example'],
				]],
			])),
		];
		$uris = [];
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->exactly(2))
			->method('sendRequest')
			->willReturnCallback(function ($request) use (&$responses, &$uris) {
				$uris[] = (string) $request->getUri();

				return array_shift($responses);
			});
		$client = new Client(['master' => 'https://api.example.com'], null, $httpClient);

		$resources = $client->resources('apps/v1', 'statefulsets')->find();

		$this->assertSame([
			'https://api.example.com/apis/apps/v1',
			'https://api.example.com/apis/apps/v1/namespaces/default/statefulsets',
		], $uris);
		$this->assertCount(1, $resources);
		$this->assertInstanceOf(DynamicResource::class, $resources->first());
		$this->assertSame('example', $resources->first()->getMetadata('name'));
	}

	public function testCreatesThirdPartyCustomResource(): void
	{
		$responses = [
			new Response(200, [], json_encode([
				'groupVersion' => 'example.io/v1',
				'resources' => [[
					'name' => 'widgets',
					'kind' => 'Widget',
					'namespaced' => true,
					'verbs' => ['create'],
				]],
			])),
			new Response(201, [], '{"apiVersion":"example.io/v1","kind":"Widget","metadata":{"name":"example"}}'),
		];
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->exactly(2))
			->method('sendRequest')
			->willReturnCallback(function ($request) use (&$responses) {
				if ($request->getMethod() === 'POST') {
					$this->assertSame(
						'https://api.example.com/apis/example.io/v1/namespaces/default/widgets',
						(string) $request->getUri()
					);
					$body = json_decode((string) $request->getBody(), true);
					$this->assertSame('example.io/v1', $body['apiVersion']);
					$this->assertSame('Widget', $body['kind']);
				}

				return array_shift($responses);
			});
		$client = new Client(['master' => 'https://api.example.com'], null, $httpClient);
		$widget = new DynamicResource([
			'apiVersion' => 'example.io/v1',
			'kind' => 'Widget',
			'metadata' => ['name' => 'example'],
			'spec' => ['size' => 3],
		]);

		$response = $client->resources('example.io/v1', 'widgets')->create($widget);

		$this->assertSame('Widget', $response['kind']);
	}

	public function testRequestsArbitrarySubresource(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->with($this->callback(function ($request): bool {
				$this->assertSame('GET', $request->getMethod());
				$this->assertSame(
					'https://api.example.com/api/v1/namespaces/default/pods/example/status',
					(string) $request->getUri()
				);

				return true;
			}))
			->willReturn(new Response(200, [], '{"kind":"Pod"}'));
		$client = new Client(['master' => 'https://api.example.com'], null, $httpClient);

		$response = $client->resources('v1', 'pods', true)
			->subresource('example', 'status');

		$this->assertSame('Pod', $response['kind']);
	}

	public function testResolvesResourceByKind(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->willReturn(new Response(200, [], json_encode([
				'groupVersion' => 'storage.k8s.io/v1',
				'resources' => [[
					'name' => 'storageclasses',
					'kind' => 'StorageClass',
					'namespaced' => false,
					'verbs' => ['get', 'list'],
				]],
			])));
		$client = new Client(['master' => 'https://api.example.com'], null, $httpClient);

		$repository = $client->resourceByKind('storage.k8s.io/v1', 'StorageClass');

		$this->assertSame('storageclasses', $repository->getResourceName());
		$this->assertFalse($repository->isNamespaced());
	}

	public function testDiscoversEveryServedApiVersion(): void
	{
		$responses = [
			new Response(200, [], '{"versions":["v1"]}'),
			new Response(200, [], '{"groups":[{"versions":[{"groupVersion":"apps/v1","version":"v1"}]}]}'),
			new Response(200, [], '{"groupVersion":"v1","resources":[{"name":"pods","kind":"Pod","namespaced":true,"verbs":["list"]}]}'),
			new Response(200, [], '{"groupVersion":"apps/v1","resources":[{"name":"statefulsets","kind":"StatefulSet","namespaced":true,"verbs":["list"]}]}'),
		];
		$uris = [];
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->exactly(4))
			->method('sendRequest')
			->willReturnCallback(function ($request) use (&$responses, &$uris) {
				$uris[] = (string) $request->getUri();

				return array_shift($responses);
			});
		$client = new Client(['master' => 'https://api.example.com'], null, $httpClient);

		$resources = $client->discoverApiResources();

		$this->assertSame([
			'https://api.example.com/api',
			'https://api.example.com/apis',
			'https://api.example.com/api/v1',
			'https://api.example.com/apis/apps/v1',
		], $uris);
		$this->assertSame(['Pod', 'StatefulSet'], array_column($resources, 'kind'));
	}

	public function testUnknownClientMethodAutomaticallyUsesDiscoveredRepository(): void
	{
		$responses = [
			new Response(200, [], '{"versions":["v1"]}'),
			new Response(200, [], '{"groups":[{"versions":[{"groupVersion":"apps/v1","version":"v1"}]}]}'),
			new Response(200, [], '{"groupVersion":"v1","resources":[{"name":"pods","kind":"Pod","namespaced":true,"verbs":["list"]}]}'),
			new Response(200, [], '{"groupVersion":"apps/v1","resources":[{"name":"statefulsets","kind":"StatefulSet","namespaced":true,"verbs":["list"]}]}'),
			new Response(200, [], '{"kind":"StatefulSetList","items":[]}'),
		];
		$uris = [];
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->exactly(5))
			->method('sendRequest')
			->willReturnCallback(function ($request) use (&$responses, &$uris) {
				$uris[] = (string) $request->getUri();

				return array_shift($responses);
			});
		$client = new Client(['master' => 'https://api.example.com'], null, $httpClient);

		$result = $client->statefulSets()->find();

		$this->assertCount(0, $result);
		$this->assertSame(
			'https://api.example.com/apis/apps/v1/namespaces/default/statefulsets',
			$uris[4]
		);
	}

	public function testApiVersionArgumentOverridesExistingTypedRepository(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->willReturn(new Response(200, [], '{"groupVersion":"events.k8s.io/v1","resources":[{"name":"events","kind":"Event","namespaced":true,"verbs":["list"]}]}'));
		$client = new Client(['master' => 'https://api.example.com'], null, $httpClient);

		$repository = $client->events('events.k8s.io/v1');

		$this->assertSame('events', $repository->getResourceName());
		$this->assertSame('events.k8s.io/v1', $repository->getDefinition()['groupVersion']);
	}

	public function testTypedRepositoryCanRequestArbitrarySubresource(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->with($this->callback(function ($request): bool {
				$this->assertSame(
					'https://api.example.com/api/v1/namespaces/default/pods/example/status',
					(string) $request->getUri()
				);

				return true;
			}))
			->willReturn(new Response(200, [], '{"kind":"Pod"}'));
		$client = new Client(['master' => 'https://api.example.com'], null, $httpClient);

		$response = $client->pods()->subresource('example', 'status');

		$this->assertSame('Pod', $response['kind']);
	}

	public function testAutomaticRepositoryUsesPreferredApiVersion(): void
	{
		$responses = [
			new Response(200, [], '{"versions":[]}'),
			new Response(200, [], '{"groups":[{"preferredVersion":{"groupVersion":"apps/v1","version":"v1"},"versions":[{"groupVersion":"apps/v1beta1","version":"v1beta1"},{"groupVersion":"apps/v1","version":"v1"}]}]}'),
			new Response(200, [], '{"groupVersion":"apps/v1","resources":[{"name":"statefulsets","kind":"StatefulSet","namespaced":true,"verbs":["list"]}]}'),
			new Response(200, [], '{"groupVersion":"apps/v1beta1","resources":[{"name":"statefulsets","kind":"StatefulSet","namespaced":true,"verbs":["list"]}]}'),
		];
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->exactly(4))
			->method('sendRequest')
			->willReturnCallback(function () use (&$responses) {
				return array_shift($responses);
			});
		$client = new Client(['master' => 'https://api.example.com'], null, $httpClient);

		$repository = $client->statefulSets();

		$this->assertSame('apps/v1', $repository->getDefinition()['groupVersion']);
	}

	public function testUniversalRepositoryDeletesCollection(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->with($this->callback(function ($request): bool {
				$this->assertSame('DELETE', $request->getMethod());
				$this->assertSame(
					'https://api.example.com/api/v1/namespaces/default/pods?labelSelector=suite%3Dexample',
					(string) $request->getUri()
				);

				return true;
			}))
			->willReturn(new Response(200, [], '{"kind":"Status","status":"Success"}'));
		$client = new Client(['master' => 'https://api.example.com'], null, $httpClient);

		$response = $client->pods()->deleteCollection(['labelSelector' => 'suite=example']);

		$this->assertSame('Success', $response['status']);
	}

	public function testUniversalRepositoryPatchesSubresource(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->with($this->callback(function ($request): bool {
				$this->assertSame('PATCH', $request->getMethod());
				$this->assertSame('application/merge-patch+json', $request->getHeaderLine('Content-Type'));
				$this->assertSame(
					'https://api.example.com/apis/example.io/v1/namespaces/default/widgets/example/status',
					(string) $request->getUri()
				);

				return true;
			}))
			->willReturn(new Response(200, [], '{"kind":"Widget"}'));
		$client = new Client(['master' => 'https://api.example.com'], null, $httpClient);

		$response = $client->resources('example.io/v1', 'widgets', true)
			->patchSubresource('example', 'status', ['status' => ['ready' => true]]);

		$this->assertSame('Widget', $response['kind']);
	}
}
