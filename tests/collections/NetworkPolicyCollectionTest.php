<?php

use Maclof\Kubernetes\Collections\NetworkPolicyCollection;

class NetworkPolicyCollectionTest extends TestCase
{
	protected array $items = [
		[],
		[],
		[],
	];

	protected function getNetworkPolicyCollection(): NetworkPolicyCollection
	{
		$podCollection = new NetworkPolicyCollection($this->items);

		return $podCollection;
	}

	public function test_get_items(): void
	{
		$podCollection = $this->getNetworkPolicyCollection();
		$items = $podCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
