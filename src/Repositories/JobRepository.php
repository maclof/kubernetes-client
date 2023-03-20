<?php namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\JobCollection;

class JobRepository extends Repository
{
	protected string $uri = 'jobs';

	protected function createCollection($response): JobCollection
	{
		return new JobCollection($response['items']);
	}
}
