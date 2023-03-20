<?php namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\NodeCollection;
use Dasann\Kubernetes\Models\Node;

class NodeRepository extends Repository
{
	protected string $uri = 'nodes';
	protected bool $namespace = false;

	protected function createCollection($response): NodeCollection
	{
		return new NodeCollection($response['items']);
	}

	public function proxy(Node $node, string $method, string $proxy_uri, array $queryParams = [])
	{
		$response = $this->client->sendRequest($method, '/' . $this->uri . '/' . $node->getMetadata('name') . '/proxy/' . $proxy_uri, $queryParams, [], $this->namespace);
		return $response;
	}
}
