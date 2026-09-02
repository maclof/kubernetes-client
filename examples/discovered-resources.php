<?php

declare(strict_types=1);

/** @var \Maclof\Kubernetes\Client $client */
$client = require __DIR__ . '/bootstrap.php';

// Unknown methods are resolved from Kubernetes API discovery.
$statefulSets = $client->statefulSets()->find();
$podDisruptionBudgets = $client->podDisruptionBudgets()->find();
$storageClasses = $client->storageClasses()->find();

// Select a non-preferred or ambiguous API version explicitly.
$newEventsApi = $client->events('events.k8s.io/v1')->find();
$autoscalingV1 = $client->horizontalPodAutoscalers('autoscaling/v1')->find();

// Resolve by exact group/version/resource or by Kind.
$leases = $client->resources('coordination.k8s.io/v1', 'leases')->find();
$runtimeClasses = $client->resourceByKind('node.k8s.io/v1', 'RuntimeClass')->find();

printf(
	"statefulSets=%d pdbs=%d storageClasses=%d events=%d hpas=%d leases=%d runtimeClasses=%d\n",
	$statefulSets->count(),
	$podDisruptionBudgets->count(),
	$storageClasses->count(),
	$newEventsApi->count(),
	$autoscalingV1->count(),
	$leases->count(),
	$runtimeClasses->count()
);
