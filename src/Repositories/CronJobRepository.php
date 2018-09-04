<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\CronJobCollection;

class CronJobRepository extends Repository
{
	protected $uri = 'cronjobs';

	protected function createCollection($response)
	{
		return new CronJobCollection($response['items']);
	}
}
