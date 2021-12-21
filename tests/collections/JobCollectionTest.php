<?php

use Maclof\Kubernetes\Collections\JobCollection;

class JobCollectionTest extends TestCase
{
	protected array $items = [
		[],
		[],
		[],
	];

	protected function getJobCollection(): JobCollection
	{
		$jobCollection = new JobCollection($this->items);

		return $jobCollection;
	}

	public function test_get_items(): void
	{
		$jobCollection = $this->getJobCollection();
		$items = $jobCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
