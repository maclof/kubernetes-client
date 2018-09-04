<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\ReplicationControllerCollection;

class ReplicationControllerRepository extends Repository
{
	protected $uri = 'replicationcontrollers';

	protected function createCollection($response)
	{
		return new ReplicationControllerCollection($response['items']);
	}
}
