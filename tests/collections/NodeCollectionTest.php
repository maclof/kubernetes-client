<?php

use Maclof\Kubernetes\Collections\NodeCollection;

class NodeCollectionTest extends TestCase
{
	protected $items = [
		[],
		[],
		[],
	];

	protected function getNodeCollection()
	{
		$nodeCollection = new NodeCollection($this->items);

		return $nodeCollection;
	}

	public function test_get_items()
	{
		$nodeCollection = $this->getNodeCollection();
		$items = $nodeCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
