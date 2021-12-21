<?php

use Maclof\Kubernetes\Models\Deployment;

class DeploymentTest extends TestCase
{
	public function test_get_schema(): void
	{
		$deployment = new Deployment;

		$schema = $deployment->getSchema();
		$fixture = $this->getFixture('deployments/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata(): void
	{
		$deployment = new Deployment([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $deployment->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}
