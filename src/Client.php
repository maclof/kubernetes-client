<?php namespace Maclof\Kubernetes;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use Maclof\Kubernetes\Collections\PodCollection;
use Maclof\Kubernetes\Collections\ReplicationControllerCollection;
use Maclof\Kubernetes\Collections\ServiceCollection;
use Maclof\Kubernetes\Models\Pod;
use Maclof\Kubernetes\Models\ReplicationController;
use Maclof\Kubernetes\Models\Service;
use Maclof\Kubernetes\Exceptions\BadRequest;

class Client
{
	/**
	 * The address of the master server.
	 * 
	 * @var string|null
	 */
	protected $master;

	/**
	 * The ca certificate.
	 * 
	 * @var string|null
	 */
	protected $caCert;

	/**
	 * The client certificate.
	 * 
	 * @var string|null
	 */
	protected $clientCert;

	/**
	 * The client key.
	 * 
	 * @var string|null
	 */
	protected $clientKey;

	/**
	 * The http client.
	 * 
	 * @var \GuzzleHttp\Client|null
	 */
	protected $client;

	/**
	 * The constructor.
	 * 
	 * @param array $options
	 */
	public function __construct(array $options = array())
	{
		$this->setOptions($options);
	}

	/**
	 * Set the options.
	 * 
	 * @param array $options
	 */
	public function setOptions(array $options)
	{
		$this->master     = $options['master'];
		$this->caCert     = $options['ca_cert'];
		$this->clientCert = $options['client_cert'];
		$this->clientKey  = $options['client_key'];
		$this->namespace  = $options['namespace'];
	}

	/**
	 * Get the http client.
	 * 
	 * @return \GuzzleHttp\Client
	 */
	protected function getHttpClient()
	{
		if ($this->client) {
			return $this->client;
		}

		return new GuzzleClient([
			'base_url' => $this->master,

			'defaults' => [
				'verify'  => $this->caCert,
				'cert'    => $this->clientCert,
				'ssl_key' => $this->clientKey,

				'headers' => [
					'Content-Type' => 'application/json',
				],
			],
		]);
	}

	/**
	 * Send a request.
	 * 
	 * @param  string $method
	 * @param  string $uri
	 * @param  mixed  $body
	 * @return array
	 */
	protected function sendRequest($method, $uri, $body = null)
	{
		$client = $this->getHttpClient();

		$request = $client->createRequest($method, '/api/v1beta3/namespaces/' . $this->namespace . $uri, [
			'body' => $body,
		]);

		try {
			$response = $client->send($request);
		}
		catch (ClientException $e) {
			throw new BadRequest($e->getMessage());
		}

		return $response->json();
	}

	/**
	 * Get the pods.
	 * 
	 * @return \Maclof\Kubernetes\Collections\PodCollection
	 */
	public function getPods()
	{
		$response = $this->sendRequest('GET', '/pods');

		return new PodCollection($response);
	}

	/**
	 * Create a pod.
	 * 
	 * @param  \Maclof\Kubernetes\Models\Pod $pod
	 * @return void
	 */
	public function createPod(Pod $pod)
	{
		$this->sendRequest('POST', '/pods', $pod->getSchema());
	}

	/**
	 * Delete a pod.
	 * 
	 * @param  \Maclof\Kubernetes\Models\Pod $pod
	 * @return void
	 */
	public function deletePod(Pod $pod)
	{
		$this->sendRequest('DELETE', '/pods/' . $pod->getMetaData('name'));
	}

	/**
	 * Get the replication controllers.
	 * 
	 * @return \Maclof\Kubernetes\Collections\ReplicationControllerCollection
	 */
	public function getReplicationControllers()
	{
		$response = $this->sendRequest('GET', '/replicationcontrollers');

		return new ReplicationControllerCollection($response);
	}

	/**
	 * Create a replication controller.
	 * 
	 * @param  \Maclof\Kubernetes\Models\ReplicationController $replicationController
	 * @return void
	 */
	public function createReplicationController(ReplicationController $replicationController)
	{
		$this->sendRequest('POST', '/replicationcontrollers', $replicationController->getSchema());
	}

	/**
	 * Delete a replication controller.
	 * 
	 * @param  \Maclof\Kubernetes\Models\ReplicationController $replicationController
	 * @return void
	 */
	public function deleteReplicationController(ReplicationController $replicationController)
	{
		$this->sendRequest('DELETE', '/replicationcontrollers/' . $replicationController->getMetaData('name'));
	}

	/**
	 * Get the services.
	 * 
	 * @return \Maclof\Kubernetes\Collections\ServiceCollection
	 */
	public function getServices()
	{
		$response = $this->sendRequest('GET', '/services');

		return new ServiceCollection($response);
	}

	/**
	 * Create a service.
	 * 
	 * @param  \Maclof\Kubernetes\Models\Service $service
	 * @return void
	 */
	public function createService(Service $service)
	{
		$this->sendRequest('POST', '/services', $service->getSchema());
	}

	/**
	 * Delete a service.
	 * 
	 * @param  \Maclof\Kubernetes\Models\Service $service
	 * @return void
	 */
	public function deleteService(Service $service)
	{
		$this->sendRequest('DELETE', '/services/' . $service->getMetaData('name'));
	}

}