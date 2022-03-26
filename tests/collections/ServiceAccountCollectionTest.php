<?php

use Maclof\Kubernetes\Collections\ServiceAccountCollection;

class ServiceAccountCollectionTest extends TestCase
{
	protected array $items = [
		[],
		[],
		[],
	];

	protected function getServiceAccountCollection(): ServiceAccountCollection
	{
		$serviceAccountCollection = new ServiceAccountCollection($this->items);

		return $serviceAccountCollection;
	}

	public function test_get_items(): void
	{
		$serviceAccountCollection = $this->getServiceAccountCollection();
		$items = $serviceAccountCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
