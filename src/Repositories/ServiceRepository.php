<?php namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\ServiceCollection;

class ServiceRepository extends Repository
{
	protected string $uri = 'services';

	protected function createCollection($response): ServiceCollection
	{
		return new ServiceCollection($response['items']);
	}
}
