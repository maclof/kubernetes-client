<?php

use Maclof\Kubernetes\Collections\DeploymentCollection;

class DeploymentCollectionTest extends TestCase
{
	protected $items = [
		[],
		[],
		[],
	];

	protected function getDeploymentCollection()
	{
		$deploymentCollection = new DeploymentCollection($this->items);

		return $deploymentCollection;
	}

	public function test_get_items()
	{
		$deploymentCollection = $this->getDeploymentCollection();
		$items = $deploymentCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
