<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\DaemonSetCollection;

class DaemonSetRepository extends Repository
{
	protected string $uri = 'daemonsets';

	protected function createCollection($response): DaemonSetCollection
	{
		return new DaemonSetCollection($response['items']);
	}
}
