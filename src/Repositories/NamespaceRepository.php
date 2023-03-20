<?php namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\NamespaceCollection;

class NamespaceRepository extends Repository
{
	protected string $uri = 'namespaces';
	protected bool $namespace = false;

	protected function createCollection($response): NamespaceCollection
	{
		return new NamespaceCollection($response['items']);
	}
}
