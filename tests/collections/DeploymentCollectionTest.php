<?php

use Maclof\Kubernetes\Collections\DeploymentCollection;

class DeploymentCollectionTest extends TestCase
{
	protected array $items = [
		[],
		[],
		[],
	];

	protected function getDeploymentCollection(): DeploymentCollection
	{
		$deploymentCollection = new DeploymentCollection($this->items);

		return $deploymentCollection;
	}

	public function test_get_items(): void
	{
		$deploymentCollection = $this->getDeploymentCollection();
		$items = $deploymentCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
