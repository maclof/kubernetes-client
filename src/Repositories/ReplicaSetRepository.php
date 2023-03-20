<?php namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\ReplicaSetCollection;

class ReplicaSetRepository extends Repository
{
	protected string $uri = 'replicasets';

	protected function createCollection($response): ReplicaSetCollection
	{
		return new ReplicaSetCollection($response['items']);
	}
}
