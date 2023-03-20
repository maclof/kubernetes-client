<?php namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\CronJobCollection;

class CronJobRepository extends Repository
{
	protected string $uri = 'cronjobs';

	protected function createCollection($response): CronJobCollection
	{
		return new CronJobCollection($response['items']);
	}
}
