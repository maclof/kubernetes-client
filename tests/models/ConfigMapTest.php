<?php

use Maclof\Kubernetes\Models\ConfigMap;

class ConfigMapTest extends TestCase
{
	public function test_get_schema(): void
	{
		$configMap = new ConfigMap;

		$schema = $configMap->getSchema();
		$fixture = $this->getFixture('config-maps/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata(): void
	{
		$configMap = new ConfigMap([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $configMap->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}
