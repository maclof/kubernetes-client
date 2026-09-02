<?php namespace Maclof\Kubernetes;

use Exception;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use InvalidArgumentException;
use BadMethodCallException;
use Maclof\Kubernetes\Exceptions\ApiServerException;
use Maclof\Kubernetes\Exceptions\BadRequestException;
use Maclof\Kubernetes\Repositories\DynamicRepository;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;

use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Exception\HttpException;
use Http\Message\RequestFactory as HttpRequestFactory;

use React\EventLoop\Factory as ReactFactory;
use React\Socket\Connector as ReactSocketConnector;
use Ratchet\Client\Connector as WebSocketConnector;

/**
 * @method DynamicRepository nodes(?string $apiVersion = null)
 * @method DynamicRepository quotas(?string $apiVersion = null)
 * @method DynamicRepository pods(?string $apiVersion = null)
 * @method DynamicRepository replicaSets(?string $apiVersion = null)
 * @method DynamicRepository replicationControllers(?string $apiVersion = null)
 * @method DynamicRepository services(?string $apiVersion = null)
 * @method DynamicRepository secrets(?string $apiVersion = null)
 * @method DynamicRepository events(?string $apiVersion = null)
 * @method DynamicRepository configMaps(?string $apiVersion = null)
 * @method DynamicRepository endpoints(?string $apiVersion = null)
 * @method DynamicRepository persistentVolumeClaims(?string $apiVersion = null)
 * @method DynamicRepository persistentVolume(?string $apiVersion = null)
 * @method DynamicRepository jobs(?string $apiVersion = null)
 * @method DynamicRepository cronJobs(?string $apiVersion = null)
 * @method DynamicRepository daemonSets(?string $apiVersion = null)
 * @method DynamicRepository deployments(?string $apiVersion = null)
 * @method DynamicRepository ingresses(?string $apiVersion = null)
 * @method DynamicRepository namespaces(?string $apiVersion = null)
 * @method DynamicRepository networkPolicies(?string $apiVersion = null)
 * @method DynamicRepository horizontalPodAutoscalers(?string $apiVersion = null)
 * @method DynamicRepository certificates(?string $apiVersion = null)
 * @method DynamicRepository issuers(?string $apiVersion = null)
 * @method DynamicRepository serviceAccounts(?string $apiVersion = null)
 * @method DynamicRepository roles(?string $apiVersion = null)
 * @method DynamicRepository roleBindings(?string $apiVersion = null)
 */
class Client
{
	/**
	 * The api version.
	 */
	protected string $apiVersion = 'v1';

	/**
	 * The address of the master server.
	 */
	protected ?string $master = null;

	/**
	 * The servide account token.
	 */
	protected ?string $token = null;

	/**
	 * The username for basic auth.
	 */
	protected ?string $username = null;

	/**
	 * The password for basic auth.
	 */
	protected ?string $password = null;

	/**
	 * The namespace.
	 */
	protected string $namespace = 'default';

	/**
	 * The http client.
	 */
	protected HttpMethodsClientInterface $httpClient;

	/**
	 * The exec channels for result messages.
	 */
	protected array $execChannels = [
		'stdin',
		'stdout',
		'stderr',
		'error',
		'resize',
	];

	/**
	 * The repository class registry.
	 */
	protected RepositoryRegistry $classRegistry;

	/**
	 * The class instances.
	 */
	protected array $classInstances = [];

	/**
	 * Discovered API resources, keyed by group/version.
	 */
	protected array $apiResourceCache = [];

	/**
	 * header for patch.
	 */
	protected array $patchHeaders = ['Content-Type' => 'application/strategic-merge-patch+json'];

	protected ?bool $verify = null;

	protected ?string $caCert = null;

	protected ?string $clientCert = null;

	protected ?string $clientKey = null;

	/**
	 * The constructor.
	 */
	public function __construct(array $options = [], ?RepositoryRegistry $repositoryRegistry = null, ?ClientInterface $httpClient = null, ?HttpRequestFactory $httpRequestFactory = null, ?StreamFactoryInterface $streamFactory = null)
	{
		$this->setOptions($options);
		$this->classRegistry = $repositoryRegistry ?: new RepositoryRegistry();
		$this->httpClient = new HttpMethodsClient(
			$httpClient ?: Psr18ClientDiscovery::find(),
			$httpRequestFactory ?: Psr17FactoryDiscovery::findRequestFactory(),
			$streamFactory ?: Psr17FactoryDiscovery::findStreamFactory()
		);
	}

	/**
	 * Set the options.
	 */
	public function setOptions(array $options, bool $reset = false): void
	{
		if ($reset) {
			$this->master = null;
			$this->verify = null;
			$this->caCert = null;
			$this->clientCert = null;
			$this->clientKey = null;
			$this->token = null;
			$this->username = null;
			$this->password = null;
			$this->namespace = 'default';
			$this->apiResourceCache = [];
		}

		if (isset($options['master'])) {
			if ($this->master !== $options['master']) {
				$this->apiResourceCache = [];
			}
			$this->master = $options['master'];
		}
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
	 * Parse a kubeconfig.
	 *
	 * @param  string|array $content Mixed type, based on the second input argument
	 * @throws \InvalidArgumentException
	 */
	public static function parseKubeconfig($content, string $contentType = 'yaml'): array
	{
		if ($contentType === 'array') {
			if (!is_array($content)) {
				throw new InvalidArgumentException('Kubeconfig is not an array.');
			}
		} elseif ($contentType === 'json') {
			if (!is_string($content)) {
				throw new InvalidArgumentException('Kubeconfig is not a string.');
			}

			if (defined('JSON_THROW_ON_ERROR')) {
				try {
					$content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
				} catch (\JsonException $e) {
					throw new InvalidArgumentException('Failed to parse JSON encoded Kubeconfig: ' . $e->getMessage(), 0, $e);
				}
			} else {
				$content = json_decode($content, true, 512);
				if ($content === false || $content === null) {
					throw new InvalidArgumentException('Failed to parse JSON encoded Kubeconfig.');
				}
			}
		} elseif ($contentType === 'yaml') {
			if (!is_string($content)) {
				throw new InvalidArgumentException('Kubeconfig is not a string.');
			}
			try {
				$content = Yaml::parse($content);
			} catch (YamlParseException $e) {
				throw new InvalidArgumentException('Failed to parse YAML encoded Kubeconfig: ' . $e->getMessage(), 0, $e);
			}
		} else {
			throw new InvalidArgumentException('Invalid Kubeconfig content type: ' . $contentType);
		}

		// TODO: support token auth?

		$contexts = [];
		if (isset($content['contexts']) && is_array($content['contexts'])) {
			foreach ($content['contexts'] as $context) {
				$contexts[$context['name']] = $context['context'];
			}
		}
		if (count($contexts) === 0) {
			throw new InvalidArgumentException('Kubeconfig parse error - No contexts are defined.');
		}

		$clusters = [];
		if (isset($content['clusters']) && is_array($content['clusters'])) {
			foreach ($content['clusters'] as $cluster) {
				$clusters[$cluster['name']] = $cluster['cluster'];
			}
		}
		if (count($clusters) === 0) {
			throw new InvalidArgumentException('Kubeconfig parse error - No clusters are defined.');
		}

		$users = [];
		if (isset($content['users']) && is_array($content['users'])) {
			foreach ($content['users'] as $user) {
				$users[$user['name']] = $user['user'];
			}
		}
		if (count($users) === 0) {
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
	 * @throws \InvalidArgumentException
	 */
	public static function parseKubeconfigFile(string $filePath): array
	{
		if (!file_exists($filePath)) {
			throw new InvalidArgumentException('Kubeconfig file does not exist at path: ' . $filePath);
		}

		return self::parseKubeconfig(file_get_contents($filePath));
	}

	/**
	 * Get a temp file path for some content.
	 */
	protected static function getTempFilePath(string $fileName, string $fileContent): string
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
	 */
	public function setNamespace(string $namespace): void
	{
		$this->namespace = $namespace;
	}

	/**
	 * Set patch header
	 */
	public function setPatchType(string $patchType = "strategic"): void
	{
		if ($patchType === "merge") {
			$this->patchHeaders = ['Content-Type' => 'application/merge-patch+json'];
		} elseif ($patchType === "json") {
			$this->patchHeaders = ['Content-Type' => 'application/json-patch+json'];
		} else {
			$this->patchHeaders = ['Content-Type' => 'application/strategic-merge-patch+json'];
		}
	}

	/**
	 * Send a request.
	 *
	 * @param  mixed $body
	 * @return mixed
	 * @throws \Maclof\Kubernetes\Exceptions\BadRequestException
	 */
	#[\ReturnTypeWillChange]
	public function sendRequest(string $method, string $uri, array $query = [], $body = null, bool $namespace = true, ?string $apiVersion = null, array $requestOptions = [])
	{
		$baseUri = $apiVersion ? ('apis/' . $apiVersion) : ('api/' . $this->apiVersion);
		if ($namespace) {
			$baseUri .= '/namespaces/' . $this->namespace;
		}

		$master = rtrim((string) $this->master, '/');
		if (in_array($uri, ['/healthz', '/version', '/api', '/apis'], true)) {
			$requestUrl = $master . $uri;
		} else {
			$requestUrl = $master . '/' . $baseUri . $uri;
		}

		if (is_array($query) && !empty($query)) {
			$requestUrl .= '?' . http_build_query($query);
		}

		try {
			if (!is_null($body)) {
				$body = is_array($body) ? json_encode($body, JSON_FORCE_OBJECT) : $body;
			}

			$headers = $method === 'PATCH' ? $this->patchHeaders : [];
			if (!is_null($body)) {
				$headers['Content-Type'] = $headers['Content-Type'] ?? 'application/json';
				$headers['Content-Length'] = (string) strlen($body);
			}

			if ($this->token) {
				$token = $this->token;
				if (file_exists($token)) {
					$token = file_get_contents($token);
				}
				$headers['Authorization'] = 'Bearer ' . trim($token);
			}

			$response = $this->httpClient->send($method, $requestUrl, $headers, $body);

			// Error Handling
			if ($response->getStatusCode() >= 500) {
				$msg = substr((string) $response->getBody(), 0, 1200); // Limit maximum chars
				throw new ApiServerException("Server responded with 500 Error: " . $msg, 500);
			}

			if (in_array($response->getStatusCode(), [401, 403], true)) {
				$msg = substr((string) $response->getBody(), 0, 1200); // Limit maximum chars
				throw new ApiServerException("Authentication Exception: " . $msg, $response->getStatusCode());
			}

			if (isset($requestOptions['stream']) && $requestOptions['stream'] === true) {
				return $response;
			}

			$responseBody = (string) $response->getBody();
			if (isset($requestOptions['decode_json']) && $requestOptions['decode_json'] === false) {
				return $responseBody;
			}

			$jsonResponse = json_decode($responseBody, true);

			if ($jsonResponse !== null && array_key_exists('code', $jsonResponse) && $this->isUpgradeRequestRequired($jsonResponse)) {
				return $this->sendUpgradeRequest($requestUrl, $query);
			}

			if ($response->getStatusCode() >= 400) {
				throw new BadRequestException($responseBody, $response->getStatusCode());
			}

			return is_array($jsonResponse) ? $jsonResponse : $responseBody;
		} catch (HttpException $e) {
			$response = $e->getResponse();

			$responseBody = (string) $response->getBody();

			if (in_array('application/json', $response->getHeader('Content-Type'), true)) {
				$jsonResponse = json_decode($responseBody, true);

				if ($this->isUpgradeRequestRequired($jsonResponse)) {
					return $this->sendUpgradeRequest($requestUrl, $query);
				}
			}

			throw new BadRequestException($responseBody, 0, $e);
		}
	}

	/**
	 * Check if an upgrade request is required.
	 */
	protected function isUpgradeRequestRequired(array $response): bool
	{
		return $response['code'] == 400 && $response['status'] === 'Failure' && $response['message'] === 'Upgrade request required';
	}

	/**
	 * Send an upgrade request and return any response messages.
	 */
	protected function sendUpgradeRequest(string $requestUri, array $query): array
	{
		$fullUrl = $requestUri . '?' . implode('&', $this->parseQueryParams($query));
		if (parse_url($fullUrl, PHP_URL_SCHEME) === 'https') {
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
	 */
	protected function parseQueryParams(array $query): array
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
	 */
	public function version(): array
	{
		return $this->sendRequest('GET', '/version');
	}

	/**
	 * Discover top-level resources and subresources served by the API server.
	 *
	 * @return array<int, array>
	 */
	public function discoverApiResources(?string $apiVersion = null): array
	{
		if ($apiVersion !== null) {
			return $this->discoverApiVersion($apiVersion);
		}

		if (isset($this->apiResourceCache['*'])) {
			return $this->apiResourceCache['*'];
		}

		$core = $this->sendRequest('GET', '/api', [], null, false);
		$groups = $this->sendRequest('GET', '/apis', [], null, false);
		$versions = isset($core['versions']) && is_array($core['versions'])
			? $core['versions']
			: [];

		foreach ($groups['groups'] ?? [] as $group) {
			if (isset($group['preferredVersion']['groupVersion'])) {
				$versions[] = $group['preferredVersion']['groupVersion'];
			}
			foreach ($group['versions'] ?? [] as $version) {
				if (isset($version['groupVersion'])) {
					$versions[] = $version['groupVersion'];
				}
			}
		}

		$resources = [];
		foreach (array_values(array_unique($versions)) as $version) {
			$resources = array_merge($resources, $this->discoverApiVersion($version));
		}

		return $this->apiResourceCache['*'] = $resources;
	}

	/**
	 * Create a repository for any built-in or third-party API resource.
	 */
	public function resources(
		string $apiVersion,
		string $resource,
		?bool $namespaced = null
	): DynamicRepository {
		$definition = [];
		if ($namespaced === null) {
			foreach ($this->discoverApiVersion($apiVersion) as $candidate) {
				if ($candidate['name'] === $resource) {
					$definition = $candidate;
					$namespaced = $candidate['namespaced'];
					break;
				}
			}

			if ($namespaced === null) {
				throw new InvalidArgumentException(sprintf(
					'Resource "%s" is not served by API version "%s".',
					$resource,
					$apiVersion
				));
			}
		}

		return new DynamicRepository($this, $apiVersion, $resource, $namespaced, $definition);
	}

	/**
	 * Resolve a repository from a Kubernetes kind in a specific API version.
	 */
	public function resourceByKind(string $apiVersion, string $kind): DynamicRepository
	{
		foreach ($this->discoverApiVersion($apiVersion) as $resource) {
			if (strpos($resource['name'], '/') === false && $resource['kind'] === $kind) {
				return new DynamicRepository(
					$this,
					$apiVersion,
					$resource['name'],
					$resource['namespaced'],
					$resource
				);
			}
		}

		throw new InvalidArgumentException(sprintf(
			'Kind "%s" is not served by API version "%s".',
			$kind,
			$apiVersion
		));
	}

	/**
	 * @return array<int, array>
	 */
	protected function discoverApiVersion(string $apiVersion): array
	{
		if (isset($this->apiResourceCache[$apiVersion])) {
			return $this->apiResourceCache[$apiVersion];
		}

		$isCore = strpos($apiVersion, '/') === false;
		$response = $this->sendRequest(
			'GET',
			'',
			[],
			null,
			false,
			$isCore ? null : $apiVersion
		);

		$resources = [];
		foreach ($response['resources'] ?? [] as $resource) {
			if (!isset($resource['name'], $resource['kind'])) {
				continue;
			}

			$resource['groupVersion'] = $apiVersion;
			$resource['namespaced'] = (bool) ($resource['namespaced'] ?? false);
			$resource['verbs'] = array_values($resource['verbs'] ?? []);
			$resources[] = $resource;
		}

		return $this->apiResourceCache[$apiVersion] = $resources;
	}

	/**
	 * Resolve a predefined alias or a discovered resource by method name.
	 *
	 * @return \Maclof\Kubernetes\Repositories\Repository
	 * @throws \BadMethodCallException
	 */
	public function __call(string $name, array $args)
	{
		$registration = isset($this->classRegistry[$name])
			? $this->classRegistry[$name]
			: null;
		$resourceName = is_array($registration)
			? $registration['resource']
			: strtolower($name);
		if (isset($args[0]) && is_string($args[0])) {
			return $this->resources($args[0], $resourceName);
		}

		if (is_array($registration)) {
			$registration['patchType'] = $registration['patchType'] ?? 'strategic';

			return new DynamicRepository(
				$this,
				$registration['apiVersion'],
				$registration['resource'],
				$registration['namespaced'],
				$registration
			);
		}

		if (is_string($registration)) {
			return $this->classInstances[$name] ?? new $registration($this);
		}

		foreach ($this->discoverApiResources() as $resource) {
			if (
				strpos($resource['name'], '/') === false
				&& $resource['name'] === $resourceName
			) {
				return new DynamicRepository(
					$this,
					$resource['groupVersion'],
					$resource['name'],
					$resource['namespaced'],
					$resource
				);
			}
		}

		throw new BadMethodCallException('No client methods exist with the name: ' . $name);
	}
}
