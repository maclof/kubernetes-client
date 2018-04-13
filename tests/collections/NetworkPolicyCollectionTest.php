<?php

use Maclof\Kubernetes\Collections\NetworkPolicyCollection;

class NetworkPolicyCollectionTest extends TestCase
{
	protected $items = [
		[],
		[],
		[],
	];

	protected function getNetworkPolicyCollection()
	{
		$podCollection = new NetworkPolicyCollection([
			'items' => $this->items,
		]);

		return $podCollection;
	}

	public function test_get_items()
	{
		$podCollection = $this->getNetworkPolicyCollection();
		$items = $podCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
