<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\JobCollection;

class JobRepository extends Repository
{
	protected $uri = 'jobs';
	
	protected $groupVersion = 'batch/v1';

	protected function createCollection($response)
	{
		return new JobCollection($response);
	}
}
