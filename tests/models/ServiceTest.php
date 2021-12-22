<?php

use Maclof\Kubernetes\Models\Service;

class ServiceTest extends TestCase
{
	public function test_get_schema(): void
	{
		$service = new Service;

		$schema = $service->getSchema();
		$fixture = $this->getFixture('services/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata(): void
	{
		$service = new Service([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $service->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}
