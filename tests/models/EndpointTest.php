<?php

use Maclof\Kubernetes\Models\Endpoint;

class EndpointTest extends TestCase
{
	public function test_get_schema(): void
	{
		$node = new Endpoint;

		$schema = $node->getSchema();
		$fixture = $this->getFixture('endpoints/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata(): void
	{
		$node = new Endpoint([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $node->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}
