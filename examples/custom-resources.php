<?php

declare(strict_types=1);

use Maclof\Kubernetes\Models\DynamicResource;

/** @var \Maclof\Kubernetes\Client $client */
$client = require __DIR__ . '/bootstrap.php';
$client->setNamespace('default');

// Assumes an example.io/v1 Widget CRD with plural name "widgets" is installed.
$widgets = $client->widgets('example.io/v1');
$name = 'client-example';
$widget = new DynamicResource([
	'apiVersion' => 'example.io/v1',
	'kind' => 'Widget',
	'metadata' => ['name' => $name],
	'spec' => ['size' => 3],
]);

$created = $widgets->create($widget);

$updated = $created;
$updated['spec']['size'] = 4;
$widgets->update(new DynamicResource($updated));

$widgets->patch(new DynamicResource([
	'apiVersion' => 'example.io/v1',
	'kind' => 'Widget',
	'metadata' => ['name' => $name],
	'spec' => ['color' => 'blue'],
]));

$widgets->applyJsonPatch($widget, [
	['op' => 'add', 'path' => '/metadata/labels', 'value' => ['managed-by' => 'kubernetes-client']],
]);

$current = $widgets->get($name);
printf("Widget size: %d\n", $current->toArray()['spec']['size']);

$widgets->watch($current, static function ($type, $object): void {
	printf("%s %s\n", $type, $object['metadata']['name'] ?? 'unknown');
}, ['timeoutSeconds' => 5]);

$widgets->delete($current);
