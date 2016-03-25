<?php

use Maclof\Kubernetes\Collections\JobCollection;

class JobCollectionTest extends TestCase
{
	protected $items = [
		[],
		[],
		[],
	];

	protected function getJobCollection()
	{
		$jobCollection = new JobCollection([
			'items' => $this->items,
		]);

		return $jobCollection;
	}

	public function test_get_items()
	{
		$jobCollection = $this->getJobCollection();
		$items = $jobCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}
}
