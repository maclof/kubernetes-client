<?php

use Maclof\Kubernetes\Models\Pod;

class PodTest extends TestCase
{
	public function test_get_schema()
	{
		$pod = new Pod;

		$schema = $pod->getSchema();
		$fixture = $this->getFixture('pods/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata()
	{
		$pod = new Pod([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $pod->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}