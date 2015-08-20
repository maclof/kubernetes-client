<?php namespace Maclof\Kubernetes;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ParseException;
use Maclof\Kubernetes\Collections\NodeCollection;
use Maclof\Kubernetes\Collections\PodCollection;
use Maclof\Kubernetes\Collections\ReplicationControllerCollection;
use Maclof\Kubernetes\Collections\ServiceCollection;
use Maclof\Kubernetes\Collections\SecretCollection;
use Maclof\Kubernetes\Models\Node;
use Maclof\Kubernetes\Models\Pod;
use Maclof\Kubernetes\Models\ReplicationController;
use Maclof\Kubernetes\Models\Service;
use Maclof\Kubernetes\Models\Secret;
use Maclof\Kubernetes\Exceptions\BadRequestException;
use Maclof\Kubernetes\Exceptions\MissingOptionException;

class Client
{
	/**
	 * The api version.
	 *
	 * @var string
	 */
	protected $apiVersion = 'v1';

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
	 * @param  string  $method
	 * @param  string  $uri
	 * @param  mixed   $body
	 * @param  boolean $namespace
	 * @return array
	 */
	protected function sendRequest($method, $uri, $body = null, $namespace = true)
	{
		$baseUri = '/api/' . $this->apiVersion;

		if ($namespace) {
			$baseUri .= '/namespaces/' . $this->namespace;
		}

		$request = $this->guzzleClient->createRequest($method, $baseUri . $uri, [
			'body' => $body,
		]);

		try {
			$response = $this->guzzleClient->send($request);
		} catch (ClientException $e) {
			throw new BadRequestException($e->getMessage());
		}

		try {
			return $response->json();
		} catch (ParseException $e) {
			return (string) $response->getBody();
		}
	}

	/**
	 * Get the nodes.
	 *
	 * @return \Maclof\Kubernetes\Collections\NodeCollection
	 */
	public function getNodes()
	{
		$response = $this->sendRequest('GET', '/nodes', null, false);

		return new NodeCollection($response);
	}

	/**
	 * Get a node.
	 *
	 * @param  string $name
	 * @return \Maclof\Kubernetes\Models\Node
	 */
	public function getNode($name)
	{
		$response = $this->sendRequest('GET', '/nodes/' . $name, null, false);

		return new Node($response);
	}

	/**
	 * Create a node.
	 *
	 * @param  \Maclof\Kubernetes\Models\Node $node
	 * @return void
	 */
	public function createNode(Node $node)
	{
		$this->sendRequest('POST', '/nodes', $node->getSchema(), false);
	}

	/**
	 * Delete a node.
	 *
	 * @param  \Maclof\Kubernetes\Models\Node $node
	 * @return void
	 */
	public function deleteNode(Node $node)
	{
		$this->sendRequest('DELETE', '/nodes/' . $node->getMetadata('name'), null, false);
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
	 * Get a pod.
	 *
	 * @param  string $name
	 * @return \Maclof\Kubernetes\Models\Pod
	 */
	public function getPod($name)
	{
		$response = $this->sendRequest('GET', '/pods/' . $name);

		return new Pod($response);
	}

	/**
	 * Get a pod's logs.
	 *
	 * @param  \Maclof\Kubernetes\Models\Pod $pod
	 * @return \Maclof\Kubernetes\Models\Pod
	 */
	public function getPodLogs(Pod $pod)
	{
		$response = $this->sendRequest('GET', '/pods/' . $pod->getMetadata('name') . '/log');

		return $response;
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
	 * Get a replication controller.
	 *
	 * @param  string $name
	 * @return \Maclof\Kubernetes\Models\ReplicationController
	 */
	public function getReplicationController($name)
	{
		$response = $this->sendRequest('GET', '/replicationcontrollers/' . $name);

		return new ReplicationController($response);
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
	 * Get a service.
	 *
	 * @param  string $name
	 * @return \Maclof\Kubernetes\Models\Service
	 */
	public function getService($name)
	{
		$response = $this->sendRequest('GET', '/services/' . $name);

		return new Service($response);
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

	/**
	 * Get the secrets.
	 *
	 * @return \Maclof\Kubernetes\Collections\SecretCollection
	 */
	public function getSecrets()
	{
		$response = $this->sendRequest('GET', '/secrets');

		return new SecretCollection($response);
	}

	/**
	 * Get a secret.
	 *
	 * @param  string $name
	 * @return \Maclof\Kubernetes\Models\Secret
	 */
	public function getSecret($name)
	{
		$response = $this->sendRequest('GET', '/secrets/' . $name);

		return new Secret($response);
	}

	/**
	 * Create a secret.
	 *
	 * @param  \Maclof\Kubernetes\Models\Secret $secret
	 * @return void
	 */
	public function createSecret(Secret $secret)
	{
		$this->sendRequest('POST', '/secrets', $secret->getSchema());
	}

	/**
	 * Delete a secret.
	 *
	 * @param  \Maclof\Kubernetes\Models\Secret $secret
	 * @return void
	 */
	public function deleteSecret(Secret $secret)
	{
		$this->sendRequest('DELETE', '/secrets/' . $secret->getMetadata('name'));
	}
}
