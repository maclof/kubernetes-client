<?php

declare(strict_types=1);

use Maclof\Kubernetes\Models\ConfigMap;

/** @var \Maclof\Kubernetes\Client $client */
$client = require __DIR__ . '/bootstrap.php';
$client->setNamespace('default');

// Existing aliases retain the same repository API and typed collections.
$pods = $client->pods()
	->setLabelSelector(['app' => 'nginx'])
	->find();

foreach ($pods as $pod) {
	printf("%s: %s\n", $pod->getMetadata('name'), $pod->toArray()['status']['phase'] ?? 'Unknown');
}

$configMap = new ConfigMap([
	'metadata' => ['name' => 'client-example'],
	'data' => ['message' => 'created'],
]);

$created = $client->configMaps()->apply($configMap);
$created['data']['message'] = 'updated';
$client->configMaps()->update(new ConfigMap($created));
$client->configMaps()->patch(new ConfigMap([
	'metadata' => ['name' => 'client-example'],
	'data' => ['patched' => 'true'],
]));
$client->configMaps()->deleteByName('client-example');
