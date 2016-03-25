<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Models\Pod;
use Maclof\Kubernetes\Collections\PodCollection;

class PodRepository extends Repository
{
	protected $uri = 'pods';

	protected function createCollection($response)
	{
		return new PodCollection($response);
	}

	/**
	 * Get the logs for a pod.
	 *
	 * @param  \Maclof\Kubernetes\Models\Pod $pod
	 * @param  array $options
	 * @return string
	 */
	public function logs(Pod $pod, array $options = [])
	{
		$response = $this->client->sendRequest('GET', '/' . $this->uri . '/' . $pod->getMetadata('name') . '/log', $options);

		return $response;
	}
}
