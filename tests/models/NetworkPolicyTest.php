<?php

use Maclof\Kubernetes\Models\NetworkPolicy;

class NetworkPolicyTest extends TestCase
{
	public function test_get_schema()
	{
		$policy = new NetworkPolicy();

		$schema = $policy->getSchema();
		$fixture = $this->getFixture('network-policies/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata()
	{
		$policy = new NetworkPolicy([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $policy->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}
