<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Job;

class JobCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($this->getJobs(isset($data['items']) ? $data['items'] : []));
	}

	/**
	 * Get an array of jobs.
	 *
	 * @param  array  $items
	 * @return array
	 */
	protected function getJobs(array $items)
	{
		foreach ($items as &$item) {
			$item = new Job($item);
		}

		return $items;
	}
}
