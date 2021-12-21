<?php

use Maclof\Kubernetes\Collections\SecretCollection;

class SecretCollectionTest extends TestCase
{
	protected array $items = [
		[],
		[],
		[],
	];

	protected function getSecretCollection(): SecretCollection
	{
		$secretCollection = new SecretCollection($this->items);

		return $secretCollection;
	}

	public function test_get_items(): void
	{
		$secretCollection = $this->getSecretCollection();
		$items = $secretCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
