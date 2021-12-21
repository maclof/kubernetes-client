<?php

use Maclof\Kubernetes\Collections\ReplicationControllerCollection;

class ReplicationControllerCollectionTest extends TestCase
{
	protected array $items = [
		[],
		[],
		[],
	];

	protected function getReplicationControllerCollection(): ReplicationControllerCollection
	{
		$replicationControllerCollection = new ReplicationControllerCollection($this->items);

		return $replicationControllerCollection;
	}

	public function test_get_items(): void
	{
		$replicationControllerCollection = $this->getReplicationControllerCollection();
		$items = $replicationControllerCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
