<?php

use Maclof\Kubernetes\Collections\NamespaceCollection;

class NamespaceCollectionTest extends TestCase
{
	protected $items = [
		[],
		[],
		[],
	];

	protected function getNamespaceCollection()
	{
		$namespaceCollection = new NamespaceCollection($this->items);

		return $namespaceCollection;
	}

	public function test_get_items()
	{
        $namespaceCollection = $this->getNamespaceCollection();
		$items = $namespaceCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
