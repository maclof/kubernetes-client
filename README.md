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

### extensions/v1beta1
* Daemon Sets
* Deployments
* Ingresses

### networking.k8s.io/v1
* Network Policies

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


## Troubleshooting
There are times when you might be posting an incorrect configuration to the Kubernetes API. In this case, the exception errors aren't that helpful because they truncate the error message, for instance you might get,

```
Client error: `POST https://kubernetes.default/apis/extensions/v1beta1/namespaces/default/deployments` resulted in a `422 Unprocessable Entit
y` response:
{"kind":"Status","apiVersion":"v1","metadata":{},"status":"Failure","message":"Deployment.apps \"test_deploy\" is invali (truncated...)
```

In order to get more details about the error, you can catch this exception and extract the full message from the response body,
```php
try {
    $client->deployments()->create($deployment);
} catch (ClientException $e) {
    $fullMessage = $e->getResponse()->getBody()->getContents();
    echo $fullMessage;
    throw $e;
}
```

Now you'll get back,
```
"{"kind":"Status","apiVersion":"v1","metadata":{},"status":"Failure","message":"Deployment.apps \"test_deploy\" is invalid: [metadata.name: Invalid value: \"test_deploy\": a DNS-1123 subdomain must consist of lower case alphanumeric characters, '-' or '.', and must start and end with an alphanumeric character (e.g. 'example.com', regex used for validation is '[a-z0-9]([-a-z0-9]*[a-z0-9])?(\\.[a-z0-9]([-a-z0-9]*[a-z0-9])?)*'), spec.template.spec.containers: Required value]","reason":"Invalid","details":{"name":"test_deploy","group":"apps","kind":"Deployment","causes":[{"reason":"FieldValueInvalid","message":"Invalid value: \"test_deploy\": a DNS-1123 subdomain must consist of lower case alphanumeric characters, '-' or '.', and must start and end with an alphanumeric character (e.g. 'example.com', regex used for validation is '[a-z0-9]([-a-z0-9]*[a-z0-9])?(\\.[a-z0-9]([-a-z0-9]*[a-z0-9])?)*')","field":"metadata.name"},{"reason":"FieldValueRequired","message":"Required value","field":"spec.template.spec.containers"}]},"code":422}
```