<?php

use Maclof\Kubernetes\Models\Role;

class RoleTest extends TestCase
{
	public function test_get_schema(): void
	{
		$role = new Role;

		$schema = $role->getSchema();
		$fixture = $this->getFixture('roles/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata(): void
	{
		$role = new Role([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $role->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}
