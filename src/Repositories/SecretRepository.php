<?php namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\SecretCollection;

class SecretRepository extends Repository
{
	protected string $uri = 'secrets';

	protected function createCollection($response): SecretCollection
	{
		return new SecretCollection($response['items']);
	}
}
