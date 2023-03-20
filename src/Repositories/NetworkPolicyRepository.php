<?php namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\NetworkPolicyCollection;

class NetworkPolicyRepository extends Repository
{
	protected string $uri = 'networkpolicies';

	protected function createCollection($response): NetworkPolicyCollection
	{
		return new NetworkPolicyCollection($response['items']);
	}
}
