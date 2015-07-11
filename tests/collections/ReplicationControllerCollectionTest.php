<?php

use Maclof\Kubernetes\Collections\ReplicationControllerCollection;

class ReplicationControllerCollectionTest extends TestCase
{
	protected $items = [
		[],
		[],
		[],
	];

	protected function getReplicationControllerCollection()
	{
		$replicationControllerCollection = new ReplicationControllerCollection([
			'items' => $this->items,
		]);

		return $replicationControllerCollection;
	}

	public function test_get_items()
	{
		$replicationControllerCollection = $this->getReplicationControllerCollection();
		$items = $replicationControllerCollection->getItems();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}