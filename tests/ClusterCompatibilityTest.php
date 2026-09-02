<?php

use Maclof\Kubernetes\Client;
use Maclof\Kubernetes\Models\ConfigMap;
use Maclof\Kubernetes\Models\QuotaModel;
use Maclof\Kubernetes\Repositories\Utils\JSONStreamingListener;
use Maclof\Kubernetes\Repositories\Utils\JSONStreamingParser;
use Maclof\Kubernetes\Repositories\Utils\ParsingException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;

class ClusterCompatibilityTest extends TestCase
{
	public function testJsonPatchDoesNotChangeLaterPatchMediaType(): void
	{
		$mediaTypes = [];
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->exactly(2))
			->method('sendRequest')
			->willReturnCallback(function ($request) use (&$mediaTypes): Response {
				$mediaTypes[] = $request->getHeaderLine('Content-Type');

				return new Response(200, [], '{"kind":"ConfigMap"}');
			});
		$client = new Client(['master' => 'https://api.example.com'], null, $httpClient);
		$model = new ConfigMap(['metadata' => ['name' => 'example']]);

		$client->configMaps()->applyJsonPatch($model, [
			['op' => 'add', 'path' => '/data', 'value' => ['key' => 'value']],
		]);
		$client->configMaps()->patch($model);

		$this->assertSame([
			'application/json-patch+json',
			'application/strategic-merge-patch+json',
		], $mediaTypes);
	}

	public function testStreamingParserAcceptsAnyPsrStream(): void
	{
		$factory = new Psr17Factory();
		$events = [];
		$listener = new JSONStreamingListener(static function ($type, $object) use (&$events): void {
			$events[] = [$type, $object];
		});
		$parser = new JSONStreamingParser(
			$factory->createStream("{\"type\":\"ADDED\",\"object\":{\"kind\":\"Pod\"}}\n"),
			$listener
		);

		$parser->parse();

		$this->assertSame([['ADDED', ['kind' => 'Pod']]], $events);
	}

	public function testStreamingParserReportsMalformedJson(): void
	{
		$factory = new Psr17Factory();
		$listener = new JSONStreamingListener(static function (): void {
		});
		$parser = new JSONStreamingParser($factory->createStream('not-json'), $listener);

		$this->expectException(ParsingException::class);
		$this->expectExceptionMessage('line 1, character 1');

		$parser->parse();
	}

	public function testResourceQuotaUsesNamespacedEndpointAndKind(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->with($this->callback(function ($request): bool {
				$this->assertSame(
					'https://api.example.com/api/v1/namespaces/default/resourcequotas',
					(string) $request->getUri()
				);
				$this->assertSame('ResourceQuota', json_decode((string) $request->getBody(), true)['kind'] ?? null);

				return true;
			}))
			->willReturn(new Response(200, [], '{"kind":"ResourceQuota"}'));
		$client = new Client(['master' => 'https://api.example.com'], null, $httpClient);

		$client->quotas()->create(new QuotaModel(['metadata' => ['name' => 'example']]));
	}
}
