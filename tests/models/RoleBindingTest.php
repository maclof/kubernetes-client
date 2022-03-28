<?php

use Maclof\Kubernetes\Models\RoleBinding;

class RoleBindingTest extends TestCase
{
	public function test_get_schema(): void
	{
		$roleBinding = new RoleBinding;

		$schema = $roleBinding->getSchema();
		$fixture = $this->getFixture('roles-bindings/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata(): void
	{
		$roleBinding = new RoleBinding([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $roleBinding->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}
