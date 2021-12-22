<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\JobCollection;

class JobRepository extends Repository
{
	protected string $uri = 'jobs';

	protected function createCollection($response): JobCollection
	{
		return new JobCollection($response['items']);
	}
}
