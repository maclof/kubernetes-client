<?php namespace Maclof\Kubernetes;

use BadMethodCallException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
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
	 * The servide account token.
	 *
	 * @var string
	 */
	protected $token;

	/**
	 * The username for basic auth.
	 *
	 * @var string
	 */
	protected $username;

	/**
	 * The password for basic auth.
	 *
	 * @var string
	 */
	protected $password;

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
	 * The class map.
	 *
	 * @var array
	 */
	protected $classMap = [
		// v1
		'nodes'                  => 'Repositories\NodeRepository',
		'pods'                   => 'Repositories\PodRepository',
		'replicaSets'            => 'Repositories\ReplicaSetRepository',
		'replicationControllers' => 'Repositories\ReplicationControllerRepository',
		'services'               => 'Repositories\ServiceRepository',
		'secrets'                => 'Repositories\SecretRepository',
		'events'                 => 'Repositories\EventRepository',
		'configMaps'             => 'Repositories\ConfigMapRepository',
		'endpoints'              => 'Repositories\EndpointRepository',
		'persistentVolumeClaims' => 'Repositories\PersistentVolumeClaimRepository',
		
		// batch/v1
		'jobs'                   => 'Repositories\JobRepository',

		// batch/v2alpha1
		'cronJobs'               => 'Repositories\CronJobRepository',

		// extensions/v1beta1
		'daemonSets'             => 'Repositories\DaemonSetRepository',
		'deployments'            => 'Repositories\DeploymentRepository',
		'ingresses'              => 'Repositories\IngressRepository',
	];

	/**
	 * The class instances.
	 *
	 * @var array
	 */
	protected $classInstances = [];

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
		if (isset($options['username'])) {
			$this->username = $options['username'];
		}
		if (isset($options['password'])) {
			$this->password = $options['password'];
		}
		if (isset($options['namespace'])) {
			$this->namespace = $options['namespace'];
		}
	}

	/**
	 * Check if we're using guzzle 6.
	 *
	 * @return boolean
	 */
	protected function isUsingGuzzle6()
	{
		return version_compare(GuzzleClientInterface::VERSION, '6') === 1;
	}

	/**
	 * Create the guzzle client.
	 *
	 * @return \GuzzleHttp\Client
	 */
	protected function createGuzzleClient()
	{
		$options = [
			'headers' => [
				'Content-Type' => 'application/json',
			],
		];

		if ($this->caCert) {
			$options['verify'] = $this->caCert;
		}
		if ($this->clientCert) {
			$options['cert'] = $this->clientCert;
		}
		if ($this->clientKey) {
			$options['ssl_key'] = $this->clientKey;
		}
		if ($this->token) {
			$token = $this->token;
			if (file_exists($token)) {
				$token = file_get_contents($token);
			}

			$options['headers']['Authorization'] = 'Bearer ' . $token;
		}
		if ($this->username && $this->password) {
			$options['auth'] = [
				$this->username,
				$this->password,
			];
		}

		if (!$this->isUsingGuzzle6()) {
			return new GuzzleClient([
				'base_url' => $this->master,
				'defaults' => $options,
			]);
		}

		$options['base_uri'] = $this->master;

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
	 * @param  array   $query
	 * @param  mixed   $body
	 * @param  boolean $namespace
	 * @param  string  $apiVersion
	 * @return array
	 */
	public function sendRequest($method, $uri, $query = [], $body = [], $namespace = true, $apiVersion = null)
	{
		$baseUri = $apiVersion ? '/apis/' . $apiVersion : '/api/' . $this->apiVersion;
		if ($namespace) {
			$baseUri .= '/namespaces/' . $this->namespace;
		}

		$requestUri = $baseUri . $uri;
		$requestOptions = [];
		if (is_array($query) && !empty($query)) {
			$requestOptions['query'] = $query;
		}
		if ($body !== null) {
			$requestOptions['body'] = is_array($body) ? json_encode($body) : $body;
		}

		if (!$this->isUsingGuzzle6()) {
			try {
				$request = $this->guzzleClient->createRequest($method, $requestUri, $requestOptions);
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

		$response = $this->guzzleClient->request($method, $requestUri, $requestOptions);

		$bodyResponse = (string) $response->getBody();
		$jsonResponse = json_decode($bodyResponse, true);

		return is_array($jsonResponse) ? $jsonResponse : $bodyResponse;
	}

	public function __call($name, $args)
	{
		if (isset($this->classMap[$name])) {
			$class = 'Maclof\Kubernetes\\' . $this->classMap[$name];

			return isset($this->classInstances[$name]) ? $this->classInstances[$name] : new $class($this);
		}

		throw new BadMethodCallException('No client methods exist with the name: ' . $name);
	}
}
