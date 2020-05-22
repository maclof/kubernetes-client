# Kubernetes Client
[![Build Status](https://api.travis-ci.org/maclof/kubernetes-client.svg?branch=master)](https://travis-ci.org/maclof/kubernetes-client)

A PHP client for managing a Kubernetes cluster.

Last tested with v1.9.6 on a production cloud hosted cluster.


## Installation using [Composer](http://getcomposer.org/)

```bash
$ composer require maclof/kubernetes-client
```

## Supported API Features
### v1
* Nodes
* Namespaces
* Pods
* Replica Sets
* Replication Controllers
* Services
* Secrets
* Events
* Config Maps
* Endpoints
* Persistent Volume
* Persistent Volume Claims

### batch/v1
* Jobs

### batch/v1beta1
* Cron Jobs

### apps/v1
* Deployments

### extensions/v1beta1
* Daemon Sets

### networking.k8s.io/v1
* Network Policies

### networking.k8s.io/v1beta1
* Ingresses

### certmanager.k8s.io/v1alpha1
* Certificates
* Issuers

## Basic Usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Maclof\Kubernetes\Client;

$client = new Client([
	'master' => 'http://master.mycluster.com',
]);

// Find pods by label selector
$pods = $client->pods()->setLabelSelector([
	'name'    => 'test',
	'version' => 'a',
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
It allows you to query status data.

```php
$jobStartTime = $client->jobs()->find()->getJsonPath('$.status.startTime')[0];
```

## Authentication Examples

### Insecure HTTP
```php
$client = new Client([
	'master' => 'http://master.mycluster.com',
]);
```

### Using TLS Certificates (Client Certificate Validation)
```php
$client = new Client([
	'master'      => 'https://master.mycluster.com',
    	'ca_cert'     => '/etc/kubernetes/ssl/ca.crt',
    	'client_cert' => '/etc/kubernetes/ssl/client.crt',
    	'client_key'  => '/etc/kubernetes/ssl/client.key',
]);
```

### Using Basic Auth
```php
$client = new Client([
	'master'   => 'https://master.mycluster.com',
    	'username' => 'admin',
    	'password' => 'abc123',
]);
```

### Using a Service Account
```php
$client = new Client([
	'master'  => 'https://master.mycluster.com',
	'ca_cert' => '/var/run/secrets/kubernetes.io/serviceaccount/ca.crt',
	'token'   => '/var/run/secrets/kubernetes.io/serviceaccount/token',
]);
```

## Extending a library

### Custom repositories
```php
$repositories = new RepositoryRegistry();


$repositories['things'] = MyApp\Kubernetes\Repository\ThingRepository::class;

$client = new Client([
    'master' => 'https://master.mycluster.com','
], null, $repositories);

$client->things(); //ThingRepository
```

## Usage Examples

### Create/Update a Replication Controller
```php
use Maclof\Kubernetes\Models\ReplicationController;

$replicationController = new ReplicationController([
	'metadata' => [
		'name' => 'nginx-test',
		'labels' => [
			'name' => 'nginx-test',
		],
	],
	'spec' => [
		'replicas' => 1,
		'template' => [
			'metadata' => [
				'labels' => [
					'name' => 'nginx-test',
				],
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

if ($client->replicationControllers()->exists($replicationController->getMetadata('name'))) {
	$client->replicationControllers()->update($replicationController);
} else {
	$client->replicationControllers()->create($replicationController);
}
```

### Delete a Replication Controller
```php
$replicationController = $client->replicationControllers()->setLabelSelector([
	'name' => 'nginx-test',
])->first();
$client->replicationControllers()->delete($replicationController);
```

You can also specify options when performing a deletion, eg. to perform [cascading delete]( https://kubernetes.io/docs/concepts/workloads/controllers/garbage-collection/#setting-the-cascading-deletion-policy)

```php
use Maclof\Kubernetes\Models\DeleteOptions;

$client->replicationControllers()->delete(
	$replicationController,
   	new DeleteOptions(['propagationPolicy' => 'Background'])
);
```

See the API documentation for an explanation of the options:

https://kubernetes.io/docs/api-reference/v1.6/#deleteoptions-v1-meta
