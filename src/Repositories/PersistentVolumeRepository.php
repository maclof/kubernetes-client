<?php
namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\PersistentVolumeCollection;

class PersistentVolumeRepository extends Repository
{
	protected string $uri = 'persistentvolumes';

	/**
	 * @see \Maclof\Kubernetes\Repositories\Repository::createCollection()
	 */
	protected function createCollection($response): PersistentVolumeCollection
	{
		return new PersistentVolumeCollection($response['items']);
	}

	/**
	 * Send a request.
	 *
	 * @param  string  $method
	 * @param  string  $uri
	 * @param  array   $query
	 * @param  mixed   $body
	 * @param  boolean $namespace
	 * @return array
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
