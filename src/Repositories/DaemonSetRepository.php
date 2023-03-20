<?php namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\DaemonSetCollection;

class DaemonSetRepository extends Repository
{
	protected string $uri = 'daemonsets';

	protected function createCollection($response): DaemonSetCollection
	{
		return new DaemonSetCollection($response['items']);
	}
}
