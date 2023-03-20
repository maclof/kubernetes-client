<?php namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\ConfigMapCollection;

class ConfigMapRepository extends Repository
{
	protected string $uri = 'configmaps';

	protected function createCollection($response): ConfigMapCollection
	{
		return new ConfigMapCollection($response['items']);
	}
}
