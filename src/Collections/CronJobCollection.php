<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\CronJob;

class CronJobCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getCronJobs($items));
	}

	/**
	 * Get an array of cron jobs.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getCronJobs(array $items): array
	{
		foreach ($items as &$item) {
			if ($item instanceof CronJob) {
				continue;
			}
			
			$item = new CronJob($item);
		}

		return $items;
	}
}
