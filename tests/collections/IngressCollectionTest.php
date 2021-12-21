<?php

use Maclof\Kubernetes\Collections\IngressCollection;

class IngressCollectionTest extends TestCase
{
	protected array $items = [
		[],
		[],
		[],
	];

	protected function getIngressCollection(): IngressCollection
	{
		$ingressCollection = new IngressCollection($this->items);

		return $ingressCollection;
	}

	public function test_get_items(): void
	{
		$ingressCollection = $this->getIngressCollection();
		$items = $ingressCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
