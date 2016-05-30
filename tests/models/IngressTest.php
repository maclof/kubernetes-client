<?php

use Maclof\Kubernetes\Models\Ingress;

class IngressTest extends TestCase
{
	public function test_get_schema()
	{
		$ingress = new Ingress;

		$schema = $ingress->getSchema();
		$fixture = $this->getFixture('ingresses/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata()
	{
		$ingress = new Ingress([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $ingress->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}
