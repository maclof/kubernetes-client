<?php

use Maclof\Kubernetes\Collections\EndpointCollection;

class EndpointCollectionTest extends TestCase
{
	protected array $items = [
		[],
		[],
		[],
	];

	protected function getEndpointCollection(): EndpointCollection
	{
		$nodeCollection = new EndpointCollection($this->items);

		return $nodeCollection;
	}

	public function test_get_items(): void
	{
		$nodeCollection = $this->getEndpointCollection();
		$items = $nodeCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
