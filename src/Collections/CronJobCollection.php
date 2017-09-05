<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\CronJob;

class CronJobCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($this->getCronJobs(isset($data['items']) ? $data['items'] : []));
	}

	/**
	 * Get an array of cron jobs.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getCronJobs(array $items)
	{
		foreach ($items as &$item) {
			$item = new CronJob($item);
		}

		return $items;
	}
}
