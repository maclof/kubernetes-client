<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\SecretCollection;

class SecretRepository extends Repository
{
	protected string $uri = 'secrets';

	protected function createCollection($response): SecretCollection
	{
		return new SecretCollection($response['items']);
	}
}
