# Kubernetes Client

[![Tests](https://github.com/maclof/kubernetes-client/actions/workflows/tests.yml/badge.svg)](https://github.com/maclof/kubernetes-client/actions/workflows/tests.yml)

A PHP client for managing a Kubernetes cluster.

Tested with PHP 7.4 through 8.5 and Kubernetes v1.37.0.


## Installation

```bash
composer require maclof/kubernetes-client
```

## Supported API Features

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

The cert-manager entries require cert-manager to be installed; they are not
built-in Kubernetes APIs.

### Kubernetes v1.37.0 coverage gaps

Live API discovery on a kubeadm v1.37.0 cluster reports 72 top-level built-in
resource/version pairs. This client supports 23, leaving the 49 pairs below.
Subresources such as `pods/status` and third-party CRDs are excluded.

| API version | Unsupported kinds |
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

Guzzle is one option; any compatible PSR-18 HTTP client can be injected.

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

## Usage Examples

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
