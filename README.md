# Kubernetes Client

[![Tests](https://github.com/maclof/kubernetes-client/actions/workflows/tests.yml/badge.svg)](https://github.com/maclof/kubernetes-client/actions/workflows/tests.yml)

A PHP client for managing a Kubernetes cluster.

Tested with PHP 7.4 through 8.5 and Kubernetes v1.37.0.

Compatible with Laravel / Illuminate 5 through 13, subject to each version's
PHP requirements. Laravel 13 requires PHP 8.3 or newer. CI tests every supported
Illuminate major across PHP 7.4 through 8.5.


## Installation

```bash
composer require maclof/kubernetes-client guzzlehttp/guzzle
```

Guzzle is used in the examples. You can instead inject any compatible PSR-18
HTTP client.

## Supported API Features

All API resources advertised by Kubernetes discovery are supported, including
built-in resources, alternate API versions, installed third-party CRDs, and
named subresources. A single universal repository supplies CRUD, selectors,
watch, and subresource operations for every kind, including logs, exec, debug,
and proxy wherever the API server exposes those capabilities.

### Backward-compatible aliases

| API version | Kind | Repository | Scope |
| --- | --- | --- | --- |
| `v1` | `ConfigMap` | `configMaps()` | Namespaced |
| `v1` | `Endpoints` | `endpoints()` | Namespaced |
| `v1` | `Event` | `events()` | Namespaced |
| `v1` | `Namespace` | `namespaces()` | Cluster |
| `v1` | `Node` | `nodes()` | Cluster |
| `v1` | `PersistentVolume` | `persistentVolume()` | Cluster |
| `v1` | `PersistentVolumeClaim` | `persistentVolumeClaims()` | Namespaced |
| `v1` | `Pod` | `pods()` | Namespaced |
| `v1` | `ReplicationController` | `replicationControllers()` | Namespaced |
| `v1` | `ResourceQuota` | `quotas()` | Namespaced |
| `v1` | `Secret` | `secrets()` | Namespaced |
| `v1` | `Service` | `services()` | Namespaced |
| `v1` | `ServiceAccount` | `serviceAccounts()` | Namespaced |
| `apps/v1` | `DaemonSet` | `daemonSets()` | Namespaced |
| `apps/v1` | `Deployment` | `deployments()` | Namespaced |
| `apps/v1` | `ReplicaSet` | `replicaSets()` | Namespaced |
| `autoscaling/v2` | `HorizontalPodAutoscaler` | `horizontalPodAutoscalers()` | Namespaced |
| `batch/v1` | `CronJob` | `cronJobs()` | Namespaced |
| `batch/v1` | `Job` | `jobs()` | Namespaced |
| `networking.k8s.io/v1` | `Ingress` | `ingresses()` | Namespaced |
| `networking.k8s.io/v1` | `NetworkPolicy` | `networkPolicies()` | Namespaced |
| `rbac.authorization.k8s.io/v1` | `Role` | `roles()` | Namespaced |
| `rbac.authorization.k8s.io/v1` | `RoleBinding` | `roleBindings()` | Namespaced |
| `cert-manager.io/v1` | `Certificate` | `certificates()` | Namespaced |
| `cert-manager.io/v1` | `Issuer` | `issuers()` | Namespaced |

These method names and their typed model collections are retained for backward
compatibility, but they use the same universal repository as discovered APIs.
The cert-manager entries require cert-manager to be installed.

### Discovery-backed repositories

Live API discovery on a kubeadm v1.37.0 cluster reports 72 top-level built-in
resource/version pairs. Twenty-three have the aliases above; the remaining 49
are resolved automatically with the same repository and operations.

| API version | Dynamically supported kinds |
| --- | --- |
| `admissionregistration.k8s.io/v1` | `MutatingAdmissionPolicy`, `MutatingAdmissionPolicyBinding`, `MutatingWebhookConfiguration`, `ValidatingAdmissionPolicy`, `ValidatingAdmissionPolicyBinding`, `ValidatingWebhookConfiguration` |
| `apiextensions.k8s.io/v1` | `CustomResourceDefinition` |
| `apiregistration.k8s.io/v1` | `APIService` |
| `apps/v1` | `ControllerRevision`, `StatefulSet` |
| `authentication.k8s.io/v1` | `SelfSubjectReview`, `TokenReview` |
| `authorization.k8s.io/v1` | `LocalSubjectAccessReview`, `SelfSubjectAccessReview`, `SelfSubjectRulesReview`, `SubjectAccessReview` |
| `autoscaling/v1` | `HorizontalPodAutoscaler` (the client uses `autoscaling/v2`) |
| `certificates.k8s.io/v1` | `CertificateSigningRequest`, `ClusterTrustBundle`, `PodCertificateRequest` |
| `coordination.k8s.io/v1` | `Lease` |
| `v1` | `Binding`, `ComponentStatus`, `LimitRange`, `PodTemplate` |
| `discovery.k8s.io/v1` | `EndpointSlice` |
| `events.k8s.io/v1` | `Event` (the client uses core `v1`) |
| `flowcontrol.apiserver.k8s.io/v1` | `FlowSchema`, `PriorityLevelConfiguration` |
| `networking.k8s.io/v1` | `IngressClass`, `IPAddress`, `ServiceCIDR` |
| `node.k8s.io/v1` | `RuntimeClass` |
| `policy/v1` | `PodDisruptionBudget` |
| `rbac.authorization.k8s.io/v1` | `ClusterRole`, `ClusterRoleBinding` |
| `resource.k8s.io/v1` | `DeviceClass`, `DeviceTaintRule`, `ResourceClaim`, `ResourceClaimTemplate`, `ResourceSlice` |
| `scheduling.k8s.io/v1` | `PriorityClass` |
| `storage.k8s.io/v1` | `CSIDriver`, `CSINode`, `CSIStorageCapacity`, `StorageClass`, `VolumeAttachment`, `VolumeAttributesClass` |
| `storagemigration.k8s.io/v1` | `StorageVersionMigration` |

Unknown client methods are matched to discovered plural resource names. This
works for Kubernetes APIs and installed CRDs:

```php
// Built-in kind without a predefined alias
$statefulSets = $client->statefulSets()->find();

// A third-party Widget CRD discovered as example.io/v1/widgets
$widgets = $client->widgets()->find();

$widget = new Maclof\Kubernetes\Models\DynamicResource([
	'apiVersion' => 'example.io/v1',
	'kind' => 'Widget',
	'metadata' => ['name' => 'example'],
	'spec' => ['size' => 3],
]);
$client->widgets()->create($widget);
```

See [the discovered-resource example](examples/discovered-resources.php) and
[the complete CRD lifecycle example](examples/custom-resources.php) for
runnable versions.

Pass an API version when a resource name is ambiguous or a non-preferred
version is required. The explicit form also supports CRD plural names that
cannot be represented as PHP method names:

```php
$events = $client->events('events.k8s.io/v1')->find();
$widgets = $client->resources('example.io/v1', 'widgets')->find();
$storageClasses = $client->resourceByKind(
	'storage.k8s.io/v1',
	'StorageClass'
)->find();
```

Every repository can request, update, or patch named subresources:

```php
$status = $client->pods()->subresource('example', 'status');
$scale = $client->deployments()->subresource('example', 'scale');
$client->widgets()->patchSubresource(
	'example',
	'status',
	['status' => ['ready' => true]]
);
```

Logs, exec, scale, status, and proxy calls are demonstrated in
[the subresource example](examples/subresources.php).

## Basic Usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Maclof\Kubernetes\Client;

$client = new Client([
	'master' => 'https://api.example.com',
]);

// Find pods by label selector
$pods = $client->pods()->setLabelSelector([
	'name'    => 'test',
	'version' => 'a',
])->find();

// Both setLabelSelector and setFieldSelector can take an optional
// second parameter which lets you define inequality based selectors (ie using the != operator)
$pods = $client->pods()->setLabelSelector([
	'name' => 'test',
], [
	'env' => 'staging',
])->find();

// Find pods by field selector
$pods = $client->pods()->setFieldSelector([
	'metadata.name' => 'test',
])->find();

// Find first pod with label selector (same for field selector)
$pod = $client->pods()->setLabelSelector([
	'name' => 'test',
])->first();
```

### Using [JSONPath](https://github.com/FlowCommunications/JSONPath)

JSONPath expressions can query model data.

```php
$job = $client->jobs()->first();
$jobStartTime = $job->getJsonPath('$.status.startTime')[0];
```

## Runnable examples

Set `KUBECONFIG` to a readable kubeconfig, install dependencies, and run an
example from the project root:

```bash
composer install
export KUBECONFIG=/path/to/kubeconfig
php examples/discovery.php
```

| Example | Demonstrates |
| --- | --- |
| [`legacy-aliases.php`](examples/legacy-aliases.php) | Existing aliases, typed collections, selectors, and ConfigMap CRUD |
| [`discovered-resources.php`](examples/discovered-resources.php) | Auto-discovered built-ins, explicit versions, exact resources, and Kind lookup |
| [`custom-resources.php`](examples/custom-resources.php) | Third-party CRD create, get, update, patch, watch, and delete |
| [`subresources.php`](examples/subresources.php) | Status, logs, exec, scale, and proxy subresources |
| [`discovery.php`](examples/discovery.php) | Every resource, scope, and verb advertised by the API server |

See [examples/README.md](examples/README.md) for prerequisites and safety notes.

## Authentication Examples

### Kubeconfig with client certificates

```php
use Maclof\Kubernetes\Client;
use GuzzleHttp\Client as GuzzleClient;

$config = Client::parseKubeconfigFile('/path/to/kubeconfig');
$httpClient = new GuzzleClient([
	'verify' => $config['ca_cert'],
	'cert' => $config['client_cert'],
	'ssl_key' => $config['client_key'],
	'http_errors' => false,
]);
$client = new Client($config, null, $httpClient);
```

### HTTPS with certificate verification disabled

```php
use Maclof\Kubernetes\Client;
use GuzzleHttp\Client as GuzzleClient;

$httpClient = new GuzzleClient([
	'verify' => false,
	'http_errors' => false,
]);
$client = new Client([
	'master' => 'https://api.example.com',
	'verify' => false,
], null, $httpClient);
```

Use this only for controlled development clusters.

### Using a Service Account

```php
use Maclof\Kubernetes\Client;
use GuzzleHttp\Client as GuzzleClient;

$httpClient = new GuzzleClient([
	'verify' => '/var/run/secrets/kubernetes.io/serviceaccount/ca.crt',
	'http_errors' => false,
]);
$client = new Client([
	'master' => 'https://kubernetes.default.svc',
	'token' => '/var/run/secrets/kubernetes.io/serviceaccount/token',
], null, $httpClient);
```

### Parsing a kubeconfig file

```php
use Maclof\Kubernetes\Client;

// Parsing from the file data directly
$config = Client::parseKubeconfig('kubeconfig yaml data');

// Parsing from the file path
$config = Client::parseKubeconfigFile('/path/to/kubeconfig');

// Example config that may be returned
// You would then feed these options into the http/kubernetes client constructors.
$config = [
	'master' => 'https://master.mycluster.com',
	'ca_cert' => '/temp/path/ca.crt',
	'client_cert' => '/temp/path/client.crt',
	'client_key' => '/temp/path/client.key',
];
```

## Testing against a real cluster

The smoke test exercises every bundled repository as well as CRUD, selectors,
watching, pod logs, exec, ephemeral containers, and node proxy operations. It
uses a temporary namespace and removes its test resources when finished.

```bash
composer install
KUBECONFIG=/path/to/kubeconfig php scripts/cluster-smoke-test.php
```

For a disposable Ubuntu host, `scripts/provision-test-cluster.sh` provisions a
single-node kubeadm cluster with containerd, Flannel, and cert-manager. Its
version variables can be overridden in the environment before running it.

## Extending the library

### Custom repositories

```php
use Maclof\Kubernetes\Client;
use Maclof\Kubernetes\RepositoryRegistry;

$repositories = new RepositoryRegistry();
$repositories['things'] = MyApp\Kubernetes\Repository\ThingRepository::class;

$client = new Client([
	'master' => 'https://master.mycluster.com',
], $repositories);

$client->things(); // MyApp\Kubernetes\Repository\ThingRepository
```

## Deployment example

### Create or update a Deployment

Models accept attributes as an array, a JSON-encoded string, or a YAML-encoded
string. Arrays are used by default.

```php
use Maclof\Kubernetes\Models\Deployment;

$deployment = new Deployment([
	'metadata' => [
		'name' => 'nginx-test',
	],
	'spec' => [
		'replicas' => 1,
		'selector' => [
			'matchLabels' => ['app' => 'nginx-test'],
		],
		'template' => [
			'metadata' => [
				'labels' => ['app' => 'nginx-test'],
			],
			'spec' => [
				'containers' => [
					[
						'name'  => 'nginx',
						'image' => 'nginx',
						'ports' => [
							[
								'containerPort' => 80,
								'protocol'      => 'TCP',
							],
						],
					],
				],
			],
		],
	],
]);

$client->deployments()->apply($deployment);
```

### Delete a Deployment

```php
$deployment = $client->deployments()->setFieldSelector([
	'metadata.name' => 'nginx-test',
])->first();
$client->deployments()->delete($deployment);
```

Deletion options can set the propagation policy:

```php
use Maclof\Kubernetes\Models\DeleteOptions;

$client->deployments()->delete(
	$deployment,
	new DeleteOptions(['propagationPolicy' => 'Background'])
);
```
