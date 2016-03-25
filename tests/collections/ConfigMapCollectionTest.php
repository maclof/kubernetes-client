<?php

use Maclof\Kubernetes\Collections\ConfigMapCollection;

class ConfigMapCollectionTest extends TestCase
{
	protected $items = [
		[],
		[],
		[],
	];

	protected function getConfigMapCollection()
	{
		$configMapCollection = new ConfigMapCollection([
			'items' => $this->items,
		]);

		return $configMapCollection;
	}

	public function test_get_items()
	{
		$configMapCollection = $this->getConfigMapCollection();
		$items = $configMapCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
