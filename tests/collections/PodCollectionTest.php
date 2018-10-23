<?php

use Maclof\Kubernetes\Collections\PodCollection;

class PodCollectionTest extends TestCase
{
	protected $items = [
		[],
		[],
		[],
	];

	protected function getPodCollection()
	{
		$podCollection = new PodCollection($this->items);

		return $podCollection;
	}

	public function test_get_items()
	{
		$podCollection = $this->getPodCollection();
		$items = $podCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
