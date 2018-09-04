<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\SecretCollection;

class SecretRepository extends Repository
{
	protected $uri = 'secrets';

	protected function createCollection($response)
	{
		return new SecretCollection($response['items']);
	}
}
