<?php

use Maclof\Kubernetes\Models\ReplicaSet;

class ReplicaSetTest extends TestCase
{
	public function test_get_schema(): void
	{
		$replicaSet = new ReplicaSet;

		$schema = $replicaSet->getSchema();
		$fixture = $this->getFixture('replica-sets/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata(): void
	{
		$replicaSet = new ReplicaSet([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $replicaSet->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}
