<?php

use Maclof\Kubernetes\Collections\ReplicaSetCollection;

class ReplicaSetCollectionTest extends TestCase
{
	protected array $items = [
		[],
		[],
		[],
	];

	protected function getReplicaSetCollection(): ReplicaSetCollection
	{
		$replicaSetCollection = new ReplicaSetCollection($this->items);

		return $replicaSetCollection;
	}

	public function test_get_items(): void
	{
		$replicaSetCollection = $this->getReplicaSetCollection();
		$items = $replicaSetCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
