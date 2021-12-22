<?php

use Maclof\Kubernetes\Collections\ConfigMapCollection;

class ConfigMapCollectionTest extends TestCase
{
	protected array $items = [
		[],
		[],
		[],
	];

	protected function getConfigMapCollection(): ConfigMapCollection
	{
		$configMapCollection = new ConfigMapCollection($this->items);

		return $configMapCollection;
	}

	public function test_get_items(): void
	{
		$configMapCollection = $this->getConfigMapCollection();
		$items = $configMapCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
