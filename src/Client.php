<?php namespace Maclof\Kubernetes;

use GuzzleHttp\Client as GuzzleClient;

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
			],
		]);
	}

	/**
	 * Send a request.
	 * 
	 * @param  string $method
	 * @param  string $uri
	 * @return array
	 */
	protected function sendRequest($method, $uri)
	{
		$client = $this->getHttpClient();

		$request = $this->getHttpClient()->createRequest($method, '/api/v1beta3/namespaces/' . $this->namespace . $uri, [

		]);

		$response = $client->send($request);

		return $response->json();
	}

	/**
	 * Get the pods.
	 * 
	 * @return array
	 */
	public function getPods()
	{
		return $this->sendRequest('GET', '/pods');
	}

	/**
	 * Get the replication controllers.
	 * 
	 * @return array
	 */
	public function getReplicationControllers()
	{
		return $this->sendRequest('GET', '/replicationcontrollers');
	}

	/**
	 * Get the services.
	 * 
	 * @return array
	 */
	public function getServices()
	{
		return $this->sendRequest('GET', '/services');
	}


}