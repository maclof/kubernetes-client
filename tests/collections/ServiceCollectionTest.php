<?php

use Maclof\Kubernetes\Collections\ServiceCollection;

class ServiceCollectionTest extends TestCase
{
	protected array $items = [
		[],
		[],
		[],
	];

	protected function getServiceCollection(): ServiceCollection
	{
		$serviceCollection = new ServiceCollection($this->items);

		return $serviceCollection;
	}

	public function test_get_items(): void
	{
		$serviceCollection = $this->getServiceCollection();
		$items = $serviceCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
