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
use Maclof\Kubernetes\Models\Deployment;
use Maclof\Kubernetes\Models\Job;
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
	 * The beta api version.
	 *
	 * @var string
	 */
	protected $betaApiVersion = 'extensions/v1beta1';

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
	 * The token.
	 *
	 * @var string
	 */
	protected $token;

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
			$this->caCert = $options['ca_cert'];
		}
		if (isset($options['client_cert'])) {
			$this->clientCert = $options['client_cert'];
		}
		if (isset($options['client_key'])) {
			$this->clientKey = $options['client_key'];
		}
		if (isset($options['token'])) {
			$this->token = $options['token'];
		}
		if (isset($options['namespace'])) {
			$this->namespace = $options['namespace'];
		}
	}

	/**
	 * Create the guzzle client.
	 *
	 * @return \GuzzleHttp\Client
	 */
	protected function createGuzzleClient()
	{
		$options = [
			'base_url' => $this->master,

			'defaults' => [
				'headers' => [
					'Content-Type' => 'application/json',
				],
			],
		];

		if ($this->caCert) {
			$options['defaults']['verify']  = $this->caCert;
		}
		if ($this->clientCert) {
			$options['defaults']['cert']    = $this->clientCert;
		}
		if ($this->clientKey) {
			$options['defaults']['ssl_key'] = $this->clientKey;
		}
		if ($this->token && file_exists($this->token)) {
			$options['defaults']['headers']['Authorization'] = 'Bearer ' . file_get_contents($this->token);
		}

		return new GuzzleClient($options);
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
	 * @param  string  $apiVersion
	 * @return array
	 */
	protected function sendRequest($method, $uri, $body = null, $namespace = true, $apiVersion = null)
	{
		$baseUri = $apiVersion ? '/apis/' . $apiVersion : '/api/' . $this->apiVersion;

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
	 * Send a beta request.
	 *
	 * @param  string  $method
	 * @param  string  $uri
	 * @param  mixed   $body
	 * @param  boolean $namespace
	 * @return array
	 */
	protected function sendBetaRequest($method, $uri, $body = null, $namespace = true)
	{
		return $this->sendRequest($method, $uri, $body, $namespace, $this->betaApiVersion);
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
	 * Update a pod.
	 *
	 * @param  \Maclof\Kubernetes\Models\Pod $pod
	 * @return void
	 */
	public function updatePod(Pod $pod)
	{
		$this->sendRequest('PUT', '/pods/' . $pod->getMetadata('name'), $pod->getSchema());
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
	 * Update a replication controller.
	 *
	 * @param  \Maclof\Kubernetes\Models\ReplicationController $replicationController
	 * @return void
	 */
	public function updateReplicationController(ReplicationController $replicationController)
	{
		$this->sendRequest('PUT', '/replicationcontrollers/' . $replicationController->getMetadata('name'), $replicationController->getSchema());
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
	 * Update a service.
	 *
	 * @param  \Maclof\Kubernetes\Models\Service $service
	 * @return void
	 */
	public function updateService(Service $service)
	{
		$this->sendRequest('PUT', '/services/' . $service->getMetadata('name'), $service->getSchema());
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
	 * Update a secret.
	 *
	 * @param  \Maclof\Kubernetes\Models\Secret $secret
	 * @return void
	 */
	public function updateSecret(Secret $secret)
	{
		$this->sendRequest('PUT', '/secrets/' . $secret->getMetadata('name'), $secret->getSchema());
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

	/**
	 * Get the deployments.
	 *
	 * @return \Maclof\Kubernetes\Collections\DeploymentCollection
	 */
	public function getDeployments()
	{
		$response = $this->sendBetaRequest('GET', '/deployments');

		return new DeploymentCollection($response);
	}

	/**
	 * Get a deployment.
	 *
	 * @param  string $name
	 * @return \Maclof\Kubernetes\Models\Deployment
	 */
	public function getDeployment($name)
	{
		$response = $this->sendBetaRequest('GET', '/deployments/' . $name);

		return new Deployment($response);
	}

	/**
	 * Create a deployment.
	 *
	 * @param  \Maclof\Kubernetes\Models\Deployment $deployment
	 * @return void
	 */
	public function createDeployment(Deployment $deployment)
	{
		$this->sendBetaRequest('POST', '/deployments', $deployment->getSchema());
	}

	/**
	 * Update a deployment.
	 *
	 * @param  \Maclof\Kubernetes\Models\Deployment $deployment
	 * @return void
	 */
	public function updateDeployment(Deployment $deployment)
	{
		$this->sendBetaRequest('PUT', '/deployments/' . $deployment->getMetadata('name'), $deployment->getSchema());
	}

	/**
	 * Delete a deployment.
	 *
	 * @param  \Maclof\Kubernetes\Models\Deployment $deployment
	 * @return void
	 */
	public function deleteDeployment(Deployment $deployment)
	{
		$this->sendBetaRequest('DELETE', '/deployments/' . $deployment->getMetadata('name'));
	}

	/**
	 * Get the jobs.
	 *
	 * @return \Maclof\Kubernetes\Collections\JobCollection
	 */
	public function getJobs()
	{
		$response = $this->sendBetaRequest('GET', '/jobs');

		return new JobCollection($response);
	}

	/**
	 * Get a job.
	 *
	 * @param  string $name
	 * @return \Maclof\Kubernetes\Models\Job
	 */
	public function getJob($name)
	{
		$response = $this->sendBetaRequest('GET', '/jobs/' . $name);

		return new Job($response);
	}

	/**
	 * Create a job.
	 *
	 * @param  \Maclof\Kubernetes\Models\Job $job
	 * @return void
	 */
	public function createJob(Job $job)
	{
		$this->sendBetaRequest('POST', '/jobs', $job->getSchema());
	}

	/**
	 * Update a job.
	 *
	 * @param  \Maclof\Kubernetes\Models\Job $job
	 * @return void
	 */
	public function updateJob(Job $job)
	{
		$this->sendBetaRequest('PUT', '/jobs/' . $job->getMetadata('name'), $job->getSchema());
	}

	/**
	 * Delete a job.
	 *
	 * @param  \Maclof\Kubernetes\Models\Job $job
	 * @return void
	 */
	public function deleteJob(Job $job)
	{
		$this->sendBetaRequest('DELETE', '/jobs/' . $job->getMetadata('name'));
	}
}
