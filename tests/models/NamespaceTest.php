<?php

use Maclof\Kubernetes\Models\NamespaceModel;

class NamespaceTest extends TestCase
{
	public function test_get_schema()
	{
		$namespace = new NamespaceModel;

		$schema = $namespace->getSchema();
		$fixture = $this->getFixture('namespaces/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata()
	{
		$node = new NamespaceModel([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $node->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}
