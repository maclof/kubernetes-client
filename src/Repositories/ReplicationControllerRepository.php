<?php namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\ReplicationControllerCollection;

class ReplicationControllerRepository extends Repository
{
	protected string $uri = 'replicationcontrollers';

	protected function createCollection($response): ReplicationControllerCollection
	{
		return new ReplicationControllerCollection($response['items']);
	}
}
