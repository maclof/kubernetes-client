<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\JobCollection;

class JobRepository extends Repository
{
	protected $uri = 'jobs';

	protected function createCollection($response)
	{
		return new JobCollection($response);
	}
}
