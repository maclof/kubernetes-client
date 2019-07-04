<?php namespace Maclof\Kubernetes;

use BadMethodCallException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use React\EventLoop\Factory as ReactFactory;
use React\Socket\Connector as ReactSocketConnector;
use Ratchet\Client\Connector as WebSocketConnector;
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
 * @method HorizontalPodAutoscalerRepository horizontalPodAutoscalers()
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
	 * The exec channels for result messages.
	 *
	 * @var array
	 */
	protected $execChannels = [
		'stdin',
		'stdout',
		'stderr',
		'error',
		'resize',
	];

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

		// apps/v1
		'deployments'            => 'Repositories\DeploymentRepository',

		// extensions/v1beta1
		'daemonSets'             => 'Repositories\DaemonSetRepository',
		'ingresses'              => 'Repositories\IngressRepository',

		// autoscaling/v2beta1
		'horizontalPodAutoscalers'  => 'Repositories\HorizontalPodAutoscalerRepository',

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
	 * header for patch.
	 *
	 * @var array
	 */
	protected $patchHeader = ['Content-Type' => 'application/strategic-merge-patch+json'];

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
	 * Set patch header
	 *
	 * @param patch type
	 */
	public function setPatchType($patchType = "strategic")
	{
		if ($patchType == "merge") {
			$this->patchHeader = ['Content-Type' => 'application/merge-patch+json'];
		} elseif ($patchType == "json") {
			$this->patchHeader = ['Content-Type' => 'application/json-patch+json'];
		} else {
			$this->patchHeader = ['Content-Type' => 'application/strategic-merge-patch+json'];
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
	 * @throws \Maclof\Kubernetes\Exceptions\BadRequestException
	 */
	public function sendRequest($method, $uri, $query = [], $body = [], $namespace = true, $apiVersion = null)
	{
		$baseUri = $apiVersion ? 'apis/' . $apiVersion : 'api/' . $this->apiVersion;
		if ($namespace) {
			$baseUri .= '/namespaces/' . $this->namespace;
		}

		$requestUri = $baseUri . $uri;
		$requestOptions = [];
		if (is_array($query) && !empty($query)) {
			$requestOptions['query'] = $query;
		}
		if ($body !== null) {
			$requestOptions['body'] = is_array($body) ? json_encode($body, JSON_FORCE_OBJECT) : $body;
		}

		if ($method === 'PATCH') {
			$requestOptions['headers'] = $this->patchHeader;
		}

		try {
			$response = $this->guzzleClient->request($method, $requestUri, $requestOptions);

			$bodyResponse = (string) $response->getBody();
			$jsonResponse = json_decode($bodyResponse, true);

			return is_array($jsonResponse) ? $jsonResponse : $bodyResponse;
		} catch (GuzzleClientException $e) {
			$response = $e->getResponse();

			$bodyResponse = (string) $response->getBody();

			if (in_array('application/json', $response->getHeader('Content-Type'))) {
				$jsonResponse = json_decode($bodyResponse, true);

				if ($this->isUpgradeRequestRequired($jsonResponse)) {
					return $this->sendUpgradeRequest($requestUri, $query);
				}
			}

			throw new BadRequestException($bodyResponse, 0, $e);
		}
	}

	/**
	 * Check if an upgrade request is required.
	 *
	 * @param  array $response
	 * @return boolean
	 */
	protected function isUpgradeRequestRequired(array $response)
	{
		return $response['code'] == 400 && $response['status'] == 'Failure' && $response['message'] == 'Upgrade request required';
	}

	/**
	 * Send an upgrade request and return any response messages.
	 *
	 * @param  string $requestUri
	 * @param  array  $query
	 * @return array
	 */
	protected function sendUpgradeRequest($requestUri, array $query)
	{
		$fullUrl = $this->master .'/' . $requestUri . '?' . implode('&', $this->parseQueryParams($query));
		if (parse_url($fullUrl, PHP_URL_SCHEME) == 'https') {
			$fullUrl = str_replace('https://', 'wss://', $fullUrl);
		} else {
			$fullUrl = str_replace('http://', 'ws://', $fullUrl);
		}

		$headers = [];
		$socketOptions = [
			'timeout' => 20,
			'tls' => [],
		];

		if ($this->token) {
			$token = $this->token;
			if (file_exists($token)) {
				$token = file_get_contents($token);
			}

			$headers['Authorization'] = 'Bearer ' . trim($token);
		}

		if (!is_null($this->verify)) {
			$socketOptions['tls']['verify_peer'] = $this->verify;
		} elseif ($this->caCert) {
			$socketOptions['tls']['cafile'] = $this->caCert;
		}

		if ($this->clientCert) {
			$socketOptions['tls']['local_cert'] = $this->clientCert;
		}
		if ($this->clientKey) {
			$socketOptions['tls']['local_pk'] = $this->clientKey;
		}

		$loop = ReactFactory::create();

		$socketConnector = new ReactSocketConnector($loop, $socketOptions);

		$wsConnector = new WebSocketConnector($loop, $socketConnector);

		$connPromise = $wsConnector($fullUrl, ['base64.channel.k8s.io'], $headers);

		$wsConnection = null;
		$messages = [];

		$connPromise->then(function ($conn) use (&$wsConnection, &$messages) {
			$wsConnection = $conn;

			$conn->on('message', function ($message) use (&$messages) {
				$data = $message->getContents();

				$channel = $this->execChannels[substr($data, 2, 1)];
				$message = base64_decode(substr($data, 3));

				if (strlen($message) == 0) {
					return;
				}

				$messages[] = [
					'channel' => $channel,
					'message' => $message,
				];
			});
		}, function ($e) {
			throw new BadRequestException('Websocket Exception', 0, $e);
		});

		$loop->run();

		$wsConnection->close();

		return $messages;
	}

	/**
	 * Parse an array of query params.
	 *
	 * @param  array $query
	 * @return array
	 */
	protected function parseQueryParams(array $query)
	{
		$parts = [];

		foreach ($query as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $val) {
					$parts[] = $key . '=' . $val;
				}

				continue;
			}

			$parts[] = $key . '=' . $value;
		}

		return $parts;
	}

	/**
	 * Magic call method to grab a class instance.
	 *
	 * @param  string $name
	 * @param  array  $args
	 * @return \stdClass
	 * @throws \BadMethodCallException
	 */
	public function __call($name, array $args)
	{
		if (isset($this->classMap[$name])) {
			$class = 'Maclof\Kubernetes\\' . $this->classMap[$name];

			return isset($this->classInstances[$name]) ? $this->classInstances[$name] : new $class($this);
		}

		throw new BadMethodCallException('No client methods exist with the name: ' . $name);
	}
}
