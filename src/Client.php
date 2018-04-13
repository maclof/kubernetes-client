<?php namespace Maclof\Kubernetes;

use BadMethodCallException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ParseException;
use Maclof\Kubernetes\Exceptions\BadRequestException;
use Maclof\Kubernetes\Exceptions\MissingOptionException;
use Maclof\Kubernetes\Repositories\ConfigMapRepository;
use Maclof\Kubernetes\Repositories\CronJobRepository;
use Maclof\Kubernetes\Repositories\DaemonSetRepository;
use Maclof\Kubernetes\Repositories\DeploymentRepository;
use Maclof\Kubernetes\Repositories\EndpointRepository;
use Maclof\Kubernetes\Repositories\EventRepository;
use Maclof\Kubernetes\Repositories\IngressRepository;
use Maclof\Kubernetes\Repositories\JobRepository;
use Maclof\Kubernetes\Repositories\NetworkPolicyRepository;
use Maclof\Kubernetes\Repositories\NodeRepository;
use Maclof\Kubernetes\Repositories\PersistentVolumeRepository;
use Maclof\Kubernetes\Repositories\PersistentVolumeClaimRepository;
use Maclof\Kubernetes\Repositories\PodRepository;
use Maclof\Kubernetes\Repositories\QuotaRepository;
use Maclof\Kubernetes\Repositories\ReplicaSetRepository;
use Maclof\Kubernetes\Repositories\ReplicationControllerRepository;
use Maclof\Kubernetes\Repositories\SecretRepository;
use Maclof\Kubernetes\Repositories\ServiceRepository;
use Maclof\Kubernetes\Repositories\NamespaceRepository;
use Maclof\Kubernetes\Models\PersistentVolume;

/**
 * @method NodeRepository nodes()
 * @method QuotaRepository quotas()
 * @method PodRepository pods()
 * @method ReplicaSetRepository replicaSets()
 * @method ReplicationControllerRepository replicationControllers()
 * @method ServiceRepository services()
 * @method SecretRepository secrets()
 * @method EventRepository events()
 * @method ConfigMapRepository configMaps()
 * @method EndpointRepository endpoints()
 * @method PersistentVolumeClaimRepository persistentVolumeClaims()
 * @method PersistentVolumeRepository persistentVolume()
 * @method JobRepository jobs()
 * @method CronJobRepository cronJobs()
 * @method DaemonSetRepository daemonSets()
 * @method DeploymentRepository deployments()
 * @method IngressRepository ingresses()
 * @method NamespaceRepository namespaces()
 * @method NetworkPolicyRepository networkPolicies()
 */
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
	 * Whether to verify the ca certificate.
	 *
	 * @var boolean|null
	 */
	protected $verify;

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
		'quotas'                 => 'Repositories\QuotaRepository',
		'pods'                   => 'Repositories\PodRepository',
		'replicaSets'            => 'Repositories\ReplicaSetRepository',
		'replicationControllers' => 'Repositories\ReplicationControllerRepository',
		'services'               => 'Repositories\ServiceRepository',
		'secrets'                => 'Repositories\SecretRepository',
		'events'                 => 'Repositories\EventRepository',
		'configMaps'             => 'Repositories\ConfigMapRepository',
		'endpoints'              => 'Repositories\EndpointRepository',
	  'persistentVolume'       => 'Repositories\PersistentVolumeRepository',
		'persistentVolumeClaims' => 'Repositories\PersistentVolumeClaimRepository',
		'namespaces'             => 'Repositories\NamespaceRepository',

		// batch/v1
		'jobs'                   => 'Repositories\JobRepository',

		// batch/v2alpha1
		'cronJobs'               => 'Repositories\CronJobRepository',

		// extensions/v1beta1
		'daemonSets'             => 'Repositories\DaemonSetRepository',
		'deployments'            => 'Repositories\DeploymentRepository',
		'ingresses'              => 'Repositories\IngressRepository',

        // networking.k8s.io/v1
        'networkPolicies'        => 'Repositories\NetworkPolicyRepository',
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
	 * @throws MissingOptionException
	 */
	public function setOptions(array $options)
	{
		if (!isset($options['master'])) {
			throw new MissingOptionException('You must provide a "master" parameter.');
		}
		$this->master = $options['master'];

		if (isset($options['verify'])) {
			$this->verify = $options['verify'];
		} elseif (isset($options['ca_cert'])) {
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
	 * Set namespace.
	 *
	 * @param string $namespace
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;
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

		if (!is_null($this->verify)) {
			$options['verify'] = $this->verify;
		} elseif ($this->caCert) {
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

			$options['headers']['Authorization'] = 'Bearer ' . trim($token);
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
	 * @return array|string
	 * @throws \Exception
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

		if ($method === 'PATCH') {
		    $requestOptions['headers'] = ['Content-Type' => 'application/strategic-merge-patch+json'];
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
