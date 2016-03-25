<?php

use Maclof\Kubernetes\Models\Event;

class EventTest extends TestCase
{
	public function test_get_schema()
	{
		$event = new Event;

		$schema = $event->getSchema();
		$fixture = $this->getFixture('events/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata()
	{
		$event = new Event([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $event->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}
