<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\DeploymentCollection;

class DeploymentRepository extends Repository
{
	protected $uri = 'deployments';

	protected function createCollection($response)
	{
		file_put_contents('data', $response);
		return new DeploymentCollection($response['items']);
	}
}
