<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\CronJobCollection;

class CronJobRepository extends Repository
{
	protected $uri = 'cronjobs';

	protected $groupVersion = 'batch/v2alpha1';

	protected function createCollection($response)
	{
		return new CronJobCollection($response);
	}
}
