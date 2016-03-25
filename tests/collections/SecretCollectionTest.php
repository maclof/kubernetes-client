<?php

use Maclof\Kubernetes\Collections\SecretCollection;

class SecretCollectionTest extends TestCase
{
	protected $items = [
		[],
		[],
		[],
	];

	protected function getSecretCollection()
	{
		$secretCollection = new SecretCollection([
			'items' => $this->items,
		]);

		return $secretCollection;
	}

	public function test_get_items()
	{
		$secretCollection = $this->getSecretCollection();
		$items = $secretCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
