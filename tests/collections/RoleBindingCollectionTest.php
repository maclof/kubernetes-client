<?php

use Maclof\Kubernetes\Collections\RoleBindingCollection;

class RoleBindingCollectionTest extends TestCase
{
	protected array $items = [
		[],
		[],
		[],
	];

	protected function getRoleBindingCollection(): RoleBindingCollection
	{
		$roleBindingCollection = new RoleBindingCollection($this->items);

		return $roleBindingCollection;
	}

	public function test_get_items(): void
	{
		$roleBindingCollection = $this->getRoleBindingCollection();
		$items = $roleBindingCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
