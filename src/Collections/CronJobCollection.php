<?php namespace Dasann\Kubernetes\Collections;

use Dasann\Kubernetes\Models\CronJob;

class CronJobCollection extends Collection
{
	/**
	 * The constructor.
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getCronJobs($items));
	}

	/**
	 * Get an array of cron jobs.
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
