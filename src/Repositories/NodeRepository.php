<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\NodeCollection;

class NodeRepository extends Repository
{
	protected $uri = 'nodes';
	protected $namespace = false;

	protected function createCollection($response)
	{
		return new NodeCollection($response['items']);
	}
}
