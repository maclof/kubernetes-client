
# Kubernetes Client

[![Build Status](https://app.travis-ci.com/maclof/kubernetes-client.svg?branch=master)](https://app.travis-ci.com/maclof/kubernetes-client)

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

// Both setLabelSelector and setFieldSelector can take an optional
// second parameter which lets you define inequality based selectors (ie using the != operator)
$pods = $client->pods()->setLabelSelector([
	'name'    => 'test'], 
	['env'     =>  'staging']
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
use Maclof\Kubernetes\Client;
$client = new Client([
	'master' => 'http://master.mycluster.com',
]);
```

### Secure HTTPS (CA + Client Certificate Validation)
```php
use Maclof\Kubernetes\Client;
use Http\Adapter\Guzzle6\Client as Guzzle6Client;
$httpClient = Guzzle6Client::createWithConfig([
	'verify' => '/etc/kubernetes/ssl/ca.crt',
	'cert' => '/etc/kubernetes/ssl/client.crt',
	'ssl_key' => '/etc/kubernetes/ssl/client.key',
]);
$client = new Client([
	'master' => 'https://master.mycluster.com',
], null, $httpClient);
```

### Insecure HTTPS (CA Certificate Verification Disabled)
```php
use Maclof\Kubernetes\Client;
use Http\Adapter\Guzzle6\Client as Guzzle6Client;
$httpClient = Guzzle6Client::createWithConfig([
	'verify' => false,
]);
$client = new Client([
	'master' => 'https://master.mycluster.com',
], null, $httpClient);
```

### Using Basic Auth
```php
use Maclof\Kubernetes\Client;
$client = new Client([
	'master' => 'https://master.mycluster.com',
	'username' => 'admin',
	'password' => 'abc123',
]);
```

### Using a Service Account
```php
use Maclof\Kubernetes\Client;
use Http\Adapter\Guzzle6\Client as Guzzle6Client;
$httpClient = Guzzle6Client::createWithConfig([
	'verify' => '/var/run/secrets/kubernetes.io/serviceaccount/ca.crt',
]);
$client = new Client([
	'master' => 'https://master.mycluster.com',
	'token' => '/var/run/secrets/kubernetes.io/serviceaccount/token',
], null, $httpClient);
```

### Parsing a kubeconfig file
```php
use Maclof\Kubernetes\Client;

// Parsing from the file data directly
$config = Client::parseKubeConfig('kubeconfig yaml data');

// Parsing from the file path
$config = Client::parseKubeConfigFile('~/.kube/config.yml');

// Example config that may be returned
// You would then feed these options into the http/kubernetes client constructors.
$config = [
	'master' => 'https://master.mycluster.com',
	'ca_cert' => '/temp/path/ca.crt',
	'client_cert' => '/temp/path/client.crt',
	'client_key' => '/temp/path/client.key',
];
```

## Extending a library

### Custom repositories
```php
use Maclof\Kubernetes\Client;

$repositories = new RepositoryRegistry();
$repositories['things'] = MyApp\Kubernetes\Repository\ThingRepository::class;

$client = new Client([
	'master' => 'https://master.mycluster.com',
], $repositories);

$client->things(); //ThingRepository
```

## Usage Examples

### Create/Update a Replication Controller
The below example uses an array to specify the replication controller's attributes. You can specify the attributes either as an array, JSON encoded string or a YAML encoded string. The second parameter to the model constructor is the data type and defaults to array.
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
