<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\EndpointCollection;

class EndpointRepository extends Repository
{
	protected $uri = 'endpoints';

	protected function createCollection($response)
	{
		return new EndpointCollection($response);
	}
}
