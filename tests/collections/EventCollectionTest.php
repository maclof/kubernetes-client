<?php

use Maclof\Kubernetes\Collections\EventCollection;

class EventCollectionTest extends TestCase
{
	protected $items = [
		[],
		[],
		[],
	];

	protected function getEventCollection()
	{
		$eventCollection = new EventCollection($this->items);

		return $eventCollection;
	}

	public function test_get_items()
	{
		$eventCollection = $this->getEventCollection();
		$items = $eventCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
