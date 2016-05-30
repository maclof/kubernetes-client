<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\IngressCollection;

class IngressRepository extends Repository
{
	protected $uri = 'ingresses';
	protected $beta = true;

	protected function createCollection($response)
	{
		return new IngressCollection($response);
	}
}
