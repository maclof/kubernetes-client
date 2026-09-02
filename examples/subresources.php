<?php

declare(strict_types=1);

/** @var \Maclof\Kubernetes\Client $client */
$client = require __DIR__ . '/bootstrap.php';
$client->setNamespace('default');

$podName = getenv('POD') ?: 'nginx';
$deploymentName = getenv('DEPLOYMENT') ?: 'nginx';

$status = $client->pods()->subresource($podName, 'status');
printf("Pod phase: %s\n", $status['status']['phase'] ?? 'Unknown');

echo $client->pods()->logs($podName, ['tailLines' => 20]);

$messages = $client->pods()->exec($podName, [
	'command' => ['sh', '-c', 'printf exec-ok'],
	'stdin' => 'false',
	'stdout' => 'true',
	'stderr' => 'true',
	'tty' => 'false',
]);
foreach ($messages as $message) {
	printf("[%s] %s\n", $message['channel'], $message['message']);
}

$scale = $client->deployments()->subresource($deploymentName, 'scale');
printf("Current replicas: %d\n", $scale['spec']['replicas'] ?? 0);

$client->deployments()->patchSubresource(
	$deploymentName,
	'scale',
	['spec' => ['replicas' => 2]]
);

$node = $client->nodes()->first();
if ($node) {
	echo $client->nodes()->proxy($node, 'GET', 'healthz');
}
