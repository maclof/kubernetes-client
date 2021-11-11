<?php namespace Maclof\Kubernetes;

use Exception;
use Http\Client\Common\HttpMethodsClientInterface;
use InvalidArgumentException;
use BadMethodCallException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;

// use GuzzleHttp\Client as GuzzleClient;
// use GuzzleHttp\Exception\ClientException as GuzzleClientException;
// use GuzzleHttp\Exception\ServerException;
// use GuzzleHttp\Psr7\Stream;

use Http\Client\HttpClient;
use Http\Client\Common\HttpMethodsClient;
use Http\Client\Exception\TransferException as HttpTransferException;
use Http\Message\RequestFactory as HttpRequestFactory;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery as HttpMessageFactoryDiscovery;

use React\EventLoop\Factory as ReactFactory;
use React\Socket\Connector as ReactSocketConnector;
use Ratchet\Client\Connector as WebSocketConnector;
use Maclof\Kubernetes\Exceptions\ApiServerException;
use Maclof\Kubernetes\Repositories\CertificateRepository;
use Maclof\Kubernetes\Exceptions\BadRequestException;
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
 * @method CertificateRepository certificates()
 * @method IssuersRepository issuers()
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
	 * @var \Http\Client\Common\HttpMethodsClientInterface
	 */
	protected $httpClient;

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
	 * The repository class registry.
	 *
	 * @var RepositoryRegistry
	 */
	protected $classRegistry;

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
	protected $patchHeaders = ['Content-Type' => 'application/strategic-merge-patch+json'];

	/**
	 * The constructor.
	 *
	 * @param array $options
	 * @param \Maclof\Kubernetes\RepositoryRegistry|null $repositoryRegistry
	 * @param \Http\Client\HttpClient|null $httpClient
	 * @param \Http\Message\RequestFactory $httpRequestFactory
	 */
	public function __construct(array $options = [], RepositoryRegistry $repositoryRegistry = null, HttpClient $httpClient = null, HttpRequestFactory $httpRequestFactory = null)
	{
		$this->setOptions($options);
		$this->classRegistry = $repositoryRegistry ?: new RepositoryRegistry();
		$this->httpClient = new HttpMethodsClient(
			$httpClient ?: HttpClientDiscovery::find(),
			$httpRequestFactory ?: HttpMessageFactoryDiscovery::find()
		);
	}

	/**
	 * Set the options.
	 *
	 * @param  array $options
	 * @param  bool  $reset
	 */
	public function setOptions(array $options, $reset = false)
	{
		if ($reset) {
			$this->master = null;
			$this->verify = null;
			$this->token = null;
			$this->username = null;
			$this->password = null;
			$this->namespace = 'default';
		}

		if (isset($options['master'])) {
			$this->master = $options['master'];
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
	 * Parse a kubeconfig.
	 * 
	 * @param  string $content
	 * @param  string $contentType
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public static function parseKubeconfig($content, $contentType = 'yaml')
	{
		if ($contentType == 'array') {
			if (!is_array($content)) {
				throw new InvalidArgumentException('Kubeconfig is not an array.');
			}
		} elseif ($contentType == 'json') {
			if (!is_string($content)) {
				throw new InvalidArgumentException('Kubeconfig is not a string.');
			}

			try {
				$content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
			} catch (JsonException $e) {
				throw new InvalidArgumentException('Failed to parse JSON encoded Kubeconfig: ' . $e->getMessage(), 0, $e);
			}
		} elseif ($contentType == 'yaml') {
			if (!is_string($content)) {
				throw new InvalidArgumentException('Kubeconfig is not a string.');
			}
			try {
				$content = Yaml::parse($content);
			} catch (YamlParseException $e) {
				throw new InvalidArgumentException('Failed to parse YAML encoded Kubeconfig: ' . $e->getMessage(), 0, $e);
			}
		} else {
			throw new InvalidArgumentException('Invalid Kubeconfig content type: ' . $contnetType);
		}

		// TODO: support token auth?

		$contexts = [];
		if (isset($content['contexts']) && is_array($content['contexts'])) {
			foreach ($content['contexts'] as $context) {
				$contexts[$context['name']] = $context['context'];
			}
		}
		if (count($contexts) == 0) {
			throw new InvalidArgumentException('Kubeconfig parse error - No contexts are defined.');
		}

		$clusters = [];
		if (isset($content['clusters']) && is_array($content['clusters'])) {
			foreach ($content['clusters'] as $cluster) {
				$clusters[$cluster['name']] = $cluster['cluster'];
			}
		}
		if (count($clusters) == 0) {
			throw new InvalidArgumentException('Kubeconfig parse error - No clusters are defined.');
		}

		$users = [];
		if (isset($content['users']) && is_array($content['users'])) {
			foreach ($content['users'] as $user) {
				$users[$user['name']] = $user['user'];
			}
		}
		if (count($users) == 0) {
			throw new InvalidArgumentException('Kubeconfig parse error - No users are defined.');
		}

		if (!isset($content['current-context'])) {
			throw new InvalidArgumentException('Kubeconfig parse error - Missing current context attribute.');
		}
		if (!isset($contexts[$content['current-context']])) {
			throw new InvalidArgumentException('Kubeconfig parse error - The current context "' . $content['current-context'] . '" is undefined.');
		}
		$context = $contexts[$content['current-context']];

		if (!isset($context['cluster'])) {
			throw new InvalidArgumentException('Kubeconfig parse error - The current context is missing the cluster attribute.');
		}
		if (!isset($clusters[$context['cluster']])) {
			throw new InvalidArgumentException('Kubeconfig parse error - The cluster "' . $context['cluster'] . '" is undefined.');
		}
		$cluster = $clusters[$context['cluster']];

		if (!isset($context['user'])) {
			throw new InvalidArgumentException('Kubeconfig parse error - The current context is missing the user attribute.');
		}
		if (!isset($users[$context['user']])) {
			throw new InvalidArgumentException('Kubeconfig parse error - The user "' . $context['user'] . '" is undefined.');
		}
		$user = $users[$context['user']];

		$options = [];

		if (!isset($cluster['server'])) {
			throw new InvalidArgumentException('Kubeconfig parse error - The cluster "' . $context['cluster'] . '" is missing the server attribute.');
		}
		$options['master'] = $cluster['server'];

		if (isset($cluster['certificate-authority-data'])) {
			$options['ca_cert'] = self::getTempFilePath('ca-cert.pem', base64_decode($cluster['certificate-authority-data'], true));
		} elseif (strpos($options['master'], 'https://') !== false) {
			$options['verify'] = false;
		}

		if (isset($user['client-certificate-data'])) {
			$options['client_cert'] = self::getTempFilePath('client-cert.pem', base64_decode($user['client-certificate-data'], true));
		}

		if (isset($user['client-key-data'])) {
			$options['client_key'] = self::getTempFilePath('client-key.pem', base64_decode($user['client-key-data'], true));
		}

		return $options;
	}

	/**
	 * Parse a kubeconfig file.
	 * 
	 * @param  string $filePath
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public static function parseKubeconfigFile($filePath)
	{
		if (!file_exists($filePath)) {
			throw new InvalidArgumentException('Kubeconfig file does not exist at path: ' . $filePath);
		}

		return self::parseKubeconfig(file_get_contents($filePath));
	}

	/**
	 * Get a temp file path for some content.
	 *
	 * @param  string $fileName
	 * @param  string $fileContent
	 * @return string
	 */
	protected static function getTempFilePath($fileName, $fileContent)
	{
		$fileName = 'kubernetes-client-' . $fileName;

		$tempFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR  . $fileName;

		if (file_put_contents($tempFilePath, $fileContent) === false) {
			throw new Exception('Failed to write content to temp file: ' . $tempFilePath);
		}

		return $tempFilePath;
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
			$this->patchHeaders = ['Content-Type' => 'application/merge-patch+json'];
		} elseif ($patchType == "json") {
			$this->patchHeaders = ['Content-Type' => 'application/json-patch+json'];
		} else {
			$this->patchHeaders = ['Content-Type' => 'application/strategic-merge-patch+json'];
		}
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
	 * @param  array   $requestOptions
	 * @return mixed
	 * @throws \Maclof\Kubernetes\Exceptions\BadRequestException
	 */
	public function sendRequest($method, $uri, $query = [], $body = null, $namespace = true, $apiVersion = null, array $requestOptions = [])
	{
		$baseUri = $apiVersion ? 'apis/' . $apiVersion : 'api/' . $this->apiVersion;
		if ($namespace) {
			$baseUri .= '/namespaces/' . $this->namespace;
		}
		
		if ($uri === '/healthz' || $uri === '/version') {
			$requestUrl = $this->master . '/' . $uri;
		} else {
			$requestUrl = $this->master . '/' . $baseUri . $uri;
		}

		if (is_array($query) && !empty($query)) {
			$requestUrl .= '?' . http_build_query($query);
		}

		try {
			$headers = $method === 'PATCH' ? $this->patchHeaders : [];

			if ($this->token) {
				$token = $this->token;
				if (file_exists($token)) {
					$token = file_get_contents($token);
				}
				$headers['Authorization'] = 'Bearer ' . trim($token);
			}

			if (!is_null($body)) {
				$body = is_array($body) ? json_encode($body, JSON_FORCE_OBJECT) : $body;
			}

			$response = $this->httpClient->send($method, $requestUrl, $headers, $body);

			if (!empty($options['stream'])) {
				return $response;
			}

			$responseBody = (string) $response->getBody();
			$jsonResponse = json_decode($responseBody, true);

			return is_array($jsonResponse) ? $jsonResponse : $responseBody;
		} catch (HttpTransferException $e) {
			$response = $e->getResponse();

			$responseBody = (string) $response->getBody();

			if (in_array('application/json', $response->getHeader('Content-Type'))) {
				$jsonResponse = json_decode($responseBody, true);

				if ($this->isUpgradeRequestRequired($jsonResponse)) {
					return $this->sendUpgradeRequest($requestUri, $query);
				}
			}

			throw new BadRequestException($responseBody, 0, $e);
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
		} elseif ($this->username && $this->password) {
			$headers['Authorization'] = 'Basic ' . base64_encode($this->username . ':' . $this->password);
		}

		if (!is_null($this->verify)) {
			$socketOptions['tls']['verify_peer'] = $this->verify;
			$socketOptions['tls']['verify_peer_name'] = $this->verify;
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
				$data = $message->getPayload();

				$channel = $this->execChannels[substr($data, 0, 1)];
				$message = base64_decode(substr($data, 1));

				$messages[] = [
					'channel' => $channel,
					'message' => $message,
				];
			});
		}, function ($e) {
			throw new BadRequestException('Websocket Exception', 0, $e);
		});

		$loop->run();

		if ($wsConnection) {
			$wsConnection->close();
		}

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
	 * Check the health.
	 *
	 * @return array|string
	 */
	public function health()
	{
		return $this->sendRequest('GET', '/healthz');
	}
	
	/**
	 * Check the version.
	 *
	 * @return array
	 */
	public function version()
	{
		return $this->sendRequest('GET', '/version');
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
		if (isset($this->classRegistry[$name])) {
			$class = $this->classRegistry[$name];

			return isset($this->classInstances[$name]) ? $this->classInstances[$name] : new $class($this);
		}

		throw new BadMethodCallException('No client methods exist with the name: ' . $name);
	}
}
