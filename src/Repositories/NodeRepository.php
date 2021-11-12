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

	public function proxy($node, $method, $proxy_uri, array $options = [])
	{
		$response = $this->client->sendRequest($method, '/' . $this->uri . '/' . $node->getMetadata('name') . '/proxy/' . $proxy_uri, [], [], $this->namespace, $this->getApiVersion(), $options);
		return $response;
	}
}
