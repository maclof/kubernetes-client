<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\PodCollection;

class PodRepository extends Repository
{
	protected $uri = 'pods';

	protected function createCollection($response)
	{
		return new PodCollection($response);
	}
}
