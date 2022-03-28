<?php

use Maclof\Kubernetes\Collections\RoleCollection;

class RoleCollectionTest extends TestCase
{
	protected array $items = [
		[],
		[],
		[],
	];

	protected function getRoleCollection(): RoleCollection
	{
		$roleCollection = new RoleCollection($this->items);

		return $roleCollection;
	}

	public function test_get_items(): void
	{
		$roleCollection = $this->getRoleCollection();
		$items = $roleCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
