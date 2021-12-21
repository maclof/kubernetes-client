<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\QuotaCollection;

class QuotaRepository extends Repository
{
	protected string $uri = 'resourcequotas';
	protected bool $namespace = false;

	protected function createCollection($response): QuotaCollection
	{
		return new QuotaCollection($response['items']);
	}
}
