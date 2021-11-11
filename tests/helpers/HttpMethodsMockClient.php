<?php

/**
 * Use this class in unit tests to mock more easily
 */
class HttpMethodsMockClient implements \Http\Client\Common\HttpMethodsClientInterface
{
	public function sendRequest(\Psr\Http\Message\RequestInterface $request): \Psr\Http\Message\ResponseInterface
	{
		return new \Nyholm\Psr7\Response(200, [], 'dummy-mock');
	}

	public function get($uri, array $headers = []): \Psr\Http\Message\ResponseInterface
	{
		return new \Nyholm\Psr7\Response(200, [], 'dummy-mock');
	}

	public function head($uri, array $headers = []): \Psr\Http\Message\ResponseInterface
	{
		return new \Nyholm\Psr7\Response(200, [], 'dummy-mock');
	}

	public function trace($uri, array $headers = []): \Psr\Http\Message\ResponseInterface
	{
		return new \Nyholm\Psr7\Response(200, [], 'dummy-mock');
	}

	public function post($uri, array $headers = [], $body = null): \Psr\Http\Message\ResponseInterface
	{
		return new \Nyholm\Psr7\Response(200, [], 'dummy-mock');
	}

	public function put($uri, array $headers = [], $body = null): \Psr\Http\Message\ResponseInterface
	{
		return new \Nyholm\Psr7\Response(200, [], 'dummy-mock');
	}

	public function patch($uri, array $headers = [], $body = null): \Psr\Http\Message\ResponseInterface
	{
		return new \Nyholm\Psr7\Response(200, [], 'dummy-mock');
	}

	public function delete($uri, array $headers = [], $body = null): \Psr\Http\Message\ResponseInterface
	{
		return new \Nyholm\Psr7\Response(200, [], 'dummy-mock');
	}

	public function options($uri, array $headers = [], $body = null): \Psr\Http\Message\ResponseInterface
	{
		return new \Nyholm\Psr7\Response(200, [], 'dummy-mock');
	}

	public function send(string $method, $uri, array $headers = [], $body = null): \Psr\Http\Message\ResponseInterface
	{
		return new \Nyholm\Psr7\Response(200, [], 'dummy-mock');
	}
}
