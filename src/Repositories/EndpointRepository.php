<?php namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\EndpointCollection;

class EndpointRepository extends Repository
{
	protected string $uri = 'endpoints';

	protected function createCollection($response): EndpointCollection
	{
		return new EndpointCollection($response['items']);
	}
}
