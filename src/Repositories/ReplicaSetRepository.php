<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\ReplicaSetCollection;

class ReplicaSetRepository extends Repository
{
	protected $uri = 'replicasets';

	protected function createCollection($response)
	{
		return new ReplicaSetCollection($response['items']);
	}
}
