<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\NetworkPolicyCollection;

class NetworkPolicyRepository extends Repository
{
	protected $uri = 'networkpolicies';

	protected function createCollection($response)
	{
		return new NetworkPolicyCollection($response['items']);
	}
}
