# Kubernetes Client
[![Build Status](https://travis-ci.org/maclof/kubernetes-client.svg?branch=master)](https://travis-ci.org/maclof/kubernetes-client)
[![Coverage Status](https://coveralls.io/repos/github/maclof/kubernetes-client/badge.svg?branch=master)](https://coveralls.io/github/maclof/kubernetes-client?branch=master)

A PHP client for managing a Kubernetes cluster.


## Installation using [Composer](http://getcomposer.org/)

```bash
$ composer require maclof/kubernetes-client
```

## Usage

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
]);

// Find first pod with label selector (same for field selector)
$pod = $client->pods()->setLabelSelector([
	'name' => 'test',
])->first();

// Find various resources (most support label / field selectors)
$nodes = $client->nodes()->find();
$replicationControllers = $client->replicationControllers()->find();
$services = $client->services()->find();
$secrets = $client->secrets()->find();
$jobs = $client->jobs()->find();
$deployments = $client->deployments()->find();
```
