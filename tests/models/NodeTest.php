<?php

use Maclof\Kubernetes\Models\Node;

class NodeTest extends TestCase
{
	public function test_get_schema()
	{
		$node = new Node;

		$schema = $node->getSchema();
		$fixture = $this->getFixture('nodes/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata()
	{
		$node = new Node([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $node->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}
