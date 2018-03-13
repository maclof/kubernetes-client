<?php
namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\PersistentVolumeCollection;

/**
 *
 * @author avi
 *
 */
class PersistentVolumeRepository extends Repository
{
    protected $uri = 'persistentvolumes';
    /**
     * (non-PHPdoc)
     *
     * @see \Maclof\Kubernetes\Repositories\Repository::createCollection()
     *
     */
    protected function createCollection($response)
    {
        return new PersistentVolumeCollection($response);
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
	protected function sendRequest($method, $uri, $query = [], $body = [], $namespace = false)
	{
	    $namespace = false;
		$apiVersion = $this->getApiVersion();
		if ($apiVersion == 'v1') {
			$apiVersion = null;
		}

		return $this->client->sendRequest($method, $uri, $query, $body, $namespace, $apiVersion);
	}
}

