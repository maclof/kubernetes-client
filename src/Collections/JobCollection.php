<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Job;

class JobCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getJobs($items));
	}

	/**
	 * Get an array of jobs.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getJobs(array $items)
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
