<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Models\Pod;
use Maclof\Kubernetes\Collections\PodCollection;

class PodRepository extends Repository
{
	protected string $uri = 'pods';

	protected function createCollection($response): PodCollection
	{
		return new PodCollection($response['items']);
	}

	/**
	 * Get the logs for a pod.
	 */
	public function logs(Pod $pod, array $queryParams = []): string
	{
		$response = $this->client->sendRequest('GET', '/' . $this->uri . '/' . $pod->getMetadata('name') . '/log', $queryParams);
		return $response;
	}
	
	/**
	 * Execute a command on a pod.
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function exec(Pod $pod, array $queryParams = [])
	{
		$response = $this->client->sendRequest('POST', '/' . $this->uri . '/' . $pod->getMetadata('name') . '/exec', $queryParams);
		return $response;
	}

    /**
     * Attach an ephemeralContainer to a pod.
     * 
     * @param Pod $pod Pod object
     * @param array $spec array representing the relevant strategic spec
     * @see https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.23/#ephemeralcontainer-v1-core EphemeralContainer spec
     * 
     * @return array
     */
    public function debug(Pod $pod, array $spec): array
    {
        $patch = json_encode($spec);
        
        $this->client->setPatchType('strategic');

        return $this->sendRequest('PATCH', '/' . $this->uri . '/' . $pod->getMetadata('name') . '/ephemeralcontainers', [], $patch, $this->namespace);
    }
}
