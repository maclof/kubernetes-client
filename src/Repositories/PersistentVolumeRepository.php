<?php
namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\PersistentVolumeCollection;

class PersistentVolumeRepository extends Repository
{
	protected string $uri = 'persistentvolumes';

	/**
	 * @see \Dasann\Kubernetes\Repositories\Repository::createCollection()
	 */
	protected function createCollection($response): PersistentVolumeCollection
	{
		return new PersistentVolumeCollection($response['items']);
	}

	/**
	 * Send a request.
	 *
	 * @param mixed $body
	 * @return mixed
	 */
	protected function sendRequest(string $method, string $uri, array $query = [], $body = [], bool $namespace = false, array $requestOptions = [])
	{
		$namespace = false;
		$apiVersion = $this->getApiVersion();
		if ($apiVersion == 'v1') {
			$apiVersion = null;
		}

		return $this->client->sendRequest($method, $uri, $query, $body, $namespace, $apiVersion);
	}
}
