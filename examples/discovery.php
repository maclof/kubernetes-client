<?php

declare(strict_types=1);

/** @var \Maclof\Kubernetes\Client $client */
$client = require __DIR__ . '/bootstrap.php';

foreach ($client->discoverApiResources() as $resource) {
	printf(
		"%-42s %-36s %-7s %s\n",
		$resource['groupVersion'] . '/' . $resource['name'],
		$resource['kind'],
		$resource['namespaced'] ? 'namespaced' : 'cluster',
		implode(',', $resource['verbs'])
	);
}
