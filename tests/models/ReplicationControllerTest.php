<?php

use Maclof\Kubernetes\Models\ReplicationController;

class ReplicationControllerTest extends TestCase
{
	public function test_get_schema()
	{
		$replicationController = new ReplicationController;

		$schema = $replicationController->getSchema();
		$fixture = $this->getFixture('replication-controllers/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata()
	{
		$replicationController = new ReplicationController([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $replicationController->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}