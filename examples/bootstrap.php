<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use Maclof\Kubernetes\Client;

require dirname(__DIR__) . '/vendor/autoload.php';

$kubeconfig = getenv('KUBECONFIG');
if (!$kubeconfig || !is_file($kubeconfig)) {
	throw new RuntimeException('Set KUBECONFIG to a readable kubeconfig file.');
}

$config = Client::parseKubeconfigFile($kubeconfig);
$httpOptions = ['http_errors' => false];

if (isset($config['ca_cert'])) {
	$httpOptions['verify'] = $config['ca_cert'];
} elseif (isset($config['verify'])) {
	$httpOptions['verify'] = $config['verify'];
}
if (isset($config['client_cert'])) {
	$httpOptions['cert'] = $config['client_cert'];
}
if (isset($config['client_key'])) {
	$httpOptions['ssl_key'] = $config['client_key'];
}

return new Client($config, null, new GuzzleClient($httpOptions));
