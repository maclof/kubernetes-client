<?php

use Maclof\Kubernetes\Models\Secret;

class SecretTest extends TestCase
{
	public function test_get_schema(): void
	{
		$secret = new Secret;

		$schema = $secret->getSchema();
		$fixture = $this->getFixture('secrets/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata(): void
	{
		$secret = new Secret([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $secret->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}
