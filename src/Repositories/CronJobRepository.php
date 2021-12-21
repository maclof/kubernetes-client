<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\CronJobCollection;

class CronJobRepository extends Repository
{
	protected string $uri = 'cronjobs';

	protected function createCollection($response): CronJobCollection
	{
		return new CronJobCollection($response['items']);
	}
}
