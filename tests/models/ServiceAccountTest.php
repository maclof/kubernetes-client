<?php

use Dasann\Kubernetes\Models\ServiceAccount;

class ServiceAccountTest extends TestCase
{
	public function test_get_schema(): void
	{
		$serviceAccount = new ServiceAccount;

		$schema = $serviceAccount->getSchema();
		$fixture = $this->getFixture('services-accounts/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata(): void
	{
		$serviceAccount = new ServiceAccount([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $serviceAccount->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}
