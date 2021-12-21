<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Job;

class JobCollection extends Collection
{
	/**
	 * The constructor.
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getJobs($items));
	}

	/**
	 * Get an array of jobs.
	 */
	protected function getJobs(array $items): array
	{
		foreach ($items as &$item) {
			if ($item instanceof Job) {
				continue;
			}
			
			$item = new Job($item);
		}

		return $items;
	}
}
