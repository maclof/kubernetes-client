<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\ServiceCollection;

class ServiceRepository extends Repository
{
	protected string $uri = 'services';

	protected function createCollection($response): ServiceCollection
	{
		return new ServiceCollection($response['items']);
	}
}
