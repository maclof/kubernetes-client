<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\JobCollection;

class JobRepository extends Repository
{
	protected $uri = 'jobs';
	protected $beta = true;

	protected function createCollection($response)
	{
		return new JobCollection($response);
	}
}
