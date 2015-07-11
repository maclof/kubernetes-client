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
	 * The api version.
	 * 
	 * @var string
	 */
	protected $apiVersion = 'v1beta3';

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
	 * The namespace.
	 * 
	 * @var string
	 */
	protected $namespace = 'default';

	/**
	 * The http client.
	 * 
	 * @var \GuzzleHttp\Client|null
	 */
	protected $guzzleClient;

	/**
	 * The constructor.
	 * 
	 * @param array $options
	 * @param \GuzzleHttp\Client $guzzleClient
	 */
	public function __construct(array $options = array(), GuzzleClient $guzzleClient = null)
	{
		$this->setOptions($options);
		$this->guzzleClient = $guzzleClient ? $guzzleClient : $this->createGuzzleClient();
	}

	/**
	 * Set the options.
	 * 
	 * @param array $options
	 */
	public function setOptions(array $options)
	{
		if (!isset($options['master'])) {
			throw new MissingOptionException('You must provide a "master" parameter.');
		}
		$this->master = $options['master'];

		if (isset($options['ca_cert'])) {
			$this->caCert     = $options['ca_cert'];
		}
		if (isset($options['client_cert'])) {
			$this->clientCert = $options['client_cert'];
		}
		if (isset($options['client_key'])) {
			$this->clientKey  = $options['client_key'];
		}
		if (isset($options['namespace'])) {
			$this->namespace  = $options['namespace'];
		}
	}

	/**
	 * Create the guzzle client.
	 * 
	 * @return \GuzzleHttp\Client
	 */
	protected function createGuzzleClient()
	{
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
	 * Get the guzzle client.
	 * 
	 * @return \GuzzleHttp\Client|null
	 */
	public function getGuzzleClient()
	{
		return $this->guzzleClient;
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
		$request = $this->guzzleClient->createRequest($method, '/api/' . $this->apiVersion . '/namespaces/' . $this->namespace . $uri, [
			'body' => $body,
		]);

		try {
			$response = $this->guzzleClient->send($request);
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
		$this->sendRequest('DELETE', '/pods/' . $pod->getMetadata('name'));
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
		$this->sendRequest('DELETE', '/replicationcontrollers/' . $replicationController->getMetadata('name'));
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
		$this->sendRequest('DELETE', '/services/' . $service->getMetadata('name'));
	}

}