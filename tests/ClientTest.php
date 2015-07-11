<?php

use Maclof\Kubernetes\Client;
use GuzzleHttp\Client as GuzzleClient;

class ClientTest extends TestCase
{
	protected function mockGuzzleCLient()
	{
		return Mockery::mock('GuzzleHttp\Client');
	}

	public function test_create_pod()
	{
		$this->assertTrue(true);
	}
}