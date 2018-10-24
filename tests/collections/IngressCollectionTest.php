<?php

use Maclof\Kubernetes\Collections\IngressCollection;

class IngressCollectionTest extends TestCase
{
	protected $items = [
		[],
		[],
		[],
	];

	protected function getIngressCollection()
	{
		$ingressCollection = new IngressCollection($this->items);

		return $ingressCollection;
	}

	public function test_get_items()
	{
		$ingressCollection = $this->getIngressCollection();
		$items = $ingressCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
