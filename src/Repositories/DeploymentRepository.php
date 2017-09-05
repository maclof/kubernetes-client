<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\DeploymentCollection;

class DeploymentRepository extends Repository
{
	protected $uri = 'deployments';

	protected function createCollection($response)
	{
		return new DeploymentCollection($response);
	}
}
