<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use Maclof\Kubernetes\Client;
use Maclof\Kubernetes\Models\Certificate;
use Maclof\Kubernetes\Models\ConfigMap;
use Maclof\Kubernetes\Models\CronJob;
use Maclof\Kubernetes\Models\DaemonSet;
use Maclof\Kubernetes\Models\Deployment;
use Maclof\Kubernetes\Models\DynamicResource;
use Maclof\Kubernetes\Models\Endpoint;
use Maclof\Kubernetes\Models\Event;
use Maclof\Kubernetes\Models\HorizontalPodAutoscaler;
use Maclof\Kubernetes\Models\Ingress;
use Maclof\Kubernetes\Models\Issuer;
use Maclof\Kubernetes\Models\Job;
use Maclof\Kubernetes\Models\Model;
use Maclof\Kubernetes\Models\NamespaceModel;
use Maclof\Kubernetes\Models\NetworkPolicy;
use Maclof\Kubernetes\Models\PersistentVolume;
use Maclof\Kubernetes\Models\PersistentVolumeClaim;
use Maclof\Kubernetes\Models\Pod;
use Maclof\Kubernetes\Models\QuotaModel;
use Maclof\Kubernetes\Models\ReplicaSet;
use Maclof\Kubernetes\Models\ReplicationController;
use Maclof\Kubernetes\Models\Role;
use Maclof\Kubernetes\Models\RoleBinding;
use Maclof\Kubernetes\Models\Secret;
use Maclof\Kubernetes\Models\Service;
use Maclof\Kubernetes\Models\ServiceAccount;

require dirname(__DIR__) . '/vendor/autoload.php';

$kubeconfig = getenv('KUBECONFIG');
if (!$kubeconfig || !is_file($kubeconfig)) {
    fwrite(STDERR, "Set KUBECONFIG to the local test cluster configuration.\n");
    exit(2);
}

$options = Client::parseKubeconfigFile($kubeconfig);
$httpClient = new GuzzleClient([
    'timeout' => 30,
    'verify' => $options['ca_cert'],
    'cert' => $options['client_cert'],
    'ssl_key' => $options['client_key'],
    'http_errors' => false,
]);
$client = new Client($options, null, $httpClient);

$health = $client->health();
if (trim((string) $health) !== 'ok') {
    throw new RuntimeException('Unexpected API health response.');
}

$version = $client->version();
if (($version['gitVersion'] ?? null) !== 'v1.37.0') {
    throw new RuntimeException('Unexpected Kubernetes version: ' . ($version['gitVersion'] ?? 'missing'));
}

printf("PASS health\nPASS version %s\n", $version['gitVersion']);

$failures = [];
$run = static function (string $name, callable $operation) use (&$failures): void {
    try {
        $operation();
        printf("PASS %s\n", $name);
    } catch (Throwable $exception) {
        $failures[$name] = get_class($exception) . ': ' . $exception->getMessage();
        printf("FAIL %s: %s\n", $name, $exception->getMessage());
    }
};
$assertResource = static function ($response, string $kind): array {
    if (!is_array($response)) {
        throw new RuntimeException('Expected a JSON object response.');
    }
    if (($response['kind'] ?? null) === 'Status' && ($response['status'] ?? null) === 'Failure') {
        throw new RuntimeException(sprintf(
            'Kubernetes API returned %s: %s',
            $response['reason'] ?? 'Failure',
            $response['message'] ?? 'No message'
        ));
    }
    if (($response['kind'] ?? null) !== $kind) {
        throw new RuntimeException('Expected kind ' . $kind . ', got ' . ($response['kind'] ?? 'missing'));
    }

    return $response;
};

$suffix = substr(bin2hex(random_bytes(4)), 0, 8);
$namespace = 'kubernetes-client-e2e-' . $suffix;
$namespaceModel = new NamespaceModel(['metadata' => ['name' => $namespace]]);
$namespaceResponse = $client->namespaces()->create($namespaceModel);
$assertResource($namespaceResponse, 'Namespace');
$client->setNamespace($namespace);
printf("PASS namespace create %s\n", $namespace);
$persistentVolumeName = null;
$createResource = static function (
    Client $client,
    string $repository,
    Model $model,
    string $kind
) use ($assertResource): array {
    return $assertResource($client->{$repository}()->create($model), $kind);
};
$waitFor = static function (callable $condition, int $timeoutSeconds = 120): void {
    $deadline = microtime(true) + $timeoutSeconds;
    $lastMessage = 'condition was not met';
    while (microtime(true) < $deadline) {
        try {
            if ($condition()) {
                return;
            }
        } catch (Throwable $exception) {
            $lastMessage = $exception->getMessage();
        }
        sleep(2);
    }

    throw new RuntimeException('Timed out waiting for cluster state: ' . $lastMessage);
};

try {
    $configMapName = 'crud-' . $suffix;
    $configMap = new ConfigMap([
        'metadata' => ['name' => $configMapName, 'labels' => ['suite' => $suffix]],
        'data' => ['phase' => 'created'],
    ]);
    $configMapResponse = [];

    $run('repository create', function () use ($client, $configMap, $assertResource, &$configMapResponse): void {
        $configMapResponse = $assertResource($client->configMaps()->create($configMap), 'ConfigMap');
    });
    $run('repository exists', function () use ($client, $configMapName): void {
        if (!$client->configMaps()->exists($configMapName)) {
            throw new RuntimeException('Created ConfigMap was not found.');
        }
    });
    $run('label and field selectors', function () use ($client, $configMapName, $suffix): void {
        $items = $client->configMaps()
            ->setLabelSelector(['suite' => $suffix])
            ->setFieldSelector(['metadata.name' => $configMapName])
            ->find();
        if ($items->count() !== 1) {
            throw new RuntimeException('Selectors did not return exactly one ConfigMap.');
        }
    });
    $run('repository update', function () use ($client, $assertResource, &$configMapResponse): void {
        $configMapResponse['data']['phase'] = 'updated';
        $configMapResponse = $assertResource(
            $client->configMaps()->update(new ConfigMap($configMapResponse)),
            'ConfigMap'
        );
    });
    $run('repository strategic patch', function () use ($client, $configMapName, $assertResource): void {
        $response = $client->configMaps()->patch(new ConfigMap([
            'metadata' => ['name' => $configMapName],
            'data' => ['strategic' => 'works'],
        ]));
        $assertResource($response, 'ConfigMap');
    });
    $run('repository JSON patch', function () use ($client, $configMapName, $assertResource): void {
        $response = $client->configMaps()->applyJsonPatch(
            new ConfigMap(['metadata' => ['name' => $configMapName]]),
            [['op' => 'add', 'path' => '/data/jsonPatch', 'value' => 'works']]
        );
        $assertResource($response, 'ConfigMap');
    });
    $run('patch type resets after JSON patch', function () use ($client, $configMapName, $assertResource): void {
        $response = $client->configMaps()->patch(new ConfigMap([
            'metadata' => ['name' => $configMapName],
            'data' => ['afterJsonPatch' => 'works'],
        ]));
        $assertResource($response, 'ConfigMap');
    });
    $run('repository apply create', function () use ($client, $suffix, $assertResource): void {
        $response = $client->configMaps()->apply(new ConfigMap([
            'metadata' => ['name' => 'applied-' . $suffix],
            'data' => ['phase' => 'created'],
        ]));
        $assertResource($response, 'ConfigMap');
    });
    $run('repository apply patch', function () use ($client, $suffix, $assertResource): void {
        $response = $client->configMaps()->apply(new ConfigMap([
            'metadata' => ['name' => 'applied-' . $suffix],
            'data' => ['phase' => 'patched'],
        ]));
        $assertResource($response, 'ConfigMap');
    });
    $run('repository watch', function () use ($client, $configMap, $configMapName): void {
        $events = [];
        $client->configMaps()->watch($configMap, static function ($type, $object) use (&$events): void {
            $events[] = [$type, $object];
        }, ['timeoutSeconds' => 1]);
        if (!$events || ($events[0][1]['metadata']['name'] ?? null) !== $configMapName) {
            throw new RuntimeException('Watch did not stream the selected ConfigMap.');
        }
    });
    $run('repository delete model', function () use ($client, $configMap, $assertResource): void {
        $assertResource($client->configMaps()->delete($configMap), 'Status');
    });
    $run('repository delete by name', function () use ($client, $suffix, $assertResource): void {
        $assertResource($client->configMaps()->deleteByName('applied-' . $suffix), 'Status');
    });

    $run('resource quota repository', function () use ($client, $suffix, $assertResource): void {
        $response = $client->quotas()->create(new QuotaModel([
            'metadata' => ['name' => 'quota-' . $suffix],
            'spec' => ['hard' => ['pods' => '20']],
        ]));
        $assertResource($response, 'ResourceQuota');
    });
    $run('cert-manager issuer repository', function () use ($client, $suffix, $assertResource): void {
        $response = $client->issuers()->create(new Issuer([
            'metadata' => ['name' => 'issuer-' . $suffix],
            'spec' => ['selfSigned' => new stdClass()],
        ]));
        $assertResource($response, 'Issuer');
    });
    $run('cert-manager certificate repository', function () use ($client, $suffix, $assertResource): void {
        $response = $client->certificates()->create(new Certificate([
            'metadata' => ['name' => 'certificate-' . $suffix],
            'spec' => [
                'secretName' => 'certificate-' . $suffix,
                'dnsNames' => ['example.test'],
                'issuerRef' => ['name' => 'issuer-' . $suffix, 'kind' => 'Issuer'],
            ],
        ]));
        $assertResource($response, 'Certificate');
    });

    $run('service account repository', function () use ($client, $suffix, $createResource): void {
        $createResource($client, 'serviceAccounts', new ServiceAccount([
            'metadata' => ['name' => 'account-' . $suffix],
        ]), 'ServiceAccount');
    });
    $run('secret repository', function () use ($client, $suffix, $createResource): void {
        $createResource($client, 'secrets', new Secret([
            'metadata' => ['name' => 'secret-' . $suffix],
            'type' => 'Opaque',
            'stringData' => ['message' => 'integration-test'],
        ]), 'Secret');
    });
    $run('role repository', function () use ($client, $suffix, $createResource): void {
        $createResource($client, 'roles', new Role([
            'metadata' => ['name' => 'reader-' . $suffix],
            'rules' => [[
                'apiGroups' => [''],
                'resources' => ['pods'],
                'verbs' => ['get', 'list'],
            ]],
        ]), 'Role');
    });
    $run('role binding repository', function () use ($client, $suffix, $namespace, $createResource): void {
        $createResource($client, 'roleBindings', new RoleBinding([
            'metadata' => ['name' => 'reader-' . $suffix],
            'subjects' => [[
                'kind' => 'ServiceAccount',
                'name' => 'account-' . $suffix,
                'namespace' => $namespace,
            ]],
            'roleRef' => [
                'apiGroup' => 'rbac.authorization.k8s.io',
                'kind' => 'Role',
                'name' => 'reader-' . $suffix,
            ],
        ]), 'RoleBinding');
    });

    $podName = 'pod-' . $suffix;
    $podResponse = [];
    $podModel = new Pod([
        'metadata' => ['name' => $podName, 'labels' => ['app' => $podName]],
        'spec' => ['containers' => [[
            'name' => 'main',
            'image' => 'busybox:1.37.0',
            'command' => ['sh', '-c', 'echo \'{"source":"kubernetes-client","valid":true}\'; sleep 3600'],
        ]]],
    ]);
    $run('pod repository', function () use ($client, $podModel, $createResource, &$podResponse): void {
        $podResponse = $createResource($client, 'pods', $podModel, 'Pod');
    });
    $run('pod reaches Running', function () use ($client, $podName, $waitFor): void {
        $waitFor(function () use ($client, $podName): bool {
            $pod = $client->pods()->setFieldSelector(['metadata.name' => $podName])->first();
            return $pod && (($pod->toArray()['status']['phase'] ?? null) === 'Running');
        });
    });
    $run('pod logs preserve JSON text', function () use ($client, $podModel): void {
        $logs = $client->pods()->logs($podModel);
        if (trim($logs) !== '{"source":"kubernetes-client","valid":true}') {
            throw new RuntimeException('Pod logs were altered: ' . $logs);
        }
    });
    $run('pod exec', function () use ($client, $podModel): void {
        $messages = $client->pods()->exec($podModel, [
            'command' => ['echo', 'exec-ok'],
            'container' => 'main',
            'stdin' => 'false',
            'stdout' => 'true',
            'stderr' => 'true',
            'tty' => 'false',
        ]);
        $output = implode('', array_column($messages, 'message'));
        if (strpos($output, 'exec-ok') === false) {
            throw new RuntimeException('Exec output was missing: ' . $output);
        }
    });
    $run('pod ephemeral container debug', function () use ($client, $podModel): void {
        $response = $client->pods()->debug($podModel, ['spec' => ['ephemeralContainers' => [[
            'name' => 'debugger',
            'image' => 'busybox:1.37.0',
            'command' => ['sh', '-c', 'sleep 30'],
            'targetContainerName' => 'main',
            'stdin' => false,
            'tty' => false,
        ]]]]);
        if (($response['kind'] ?? null) !== 'Pod') {
            throw new RuntimeException('Ephemeral container update did not return a Pod.');
        }
    });

    $serviceName = 'service-' . $suffix;
    $run('service repository', function () use ($client, $suffix, $podName, $serviceName, $createResource): void {
        $createResource($client, 'services', new Service([
            'metadata' => ['name' => $serviceName],
            'spec' => [
                'selector' => ['app' => $podName],
                'ports' => [['name' => 'http', 'port' => 80, 'targetPort' => 8080]],
            ],
        ]), 'Service');
    });
    $run('endpoints repository', function () use ($client, $suffix, $createResource): void {
        $createResource($client, 'endpoints', new Endpoint([
            'metadata' => ['name' => 'manual-' . $suffix],
            'subsets' => [[
                'addresses' => [['ip' => '10.244.0.250']],
                'ports' => [['port' => 8080, 'protocol' => 'TCP']],
            ]],
        ]), 'Endpoints');
    });

    $selector = ['matchLabels' => ['suite' => $suffix]];
    $template = [
        'metadata' => ['labels' => ['suite' => $suffix]],
        'spec' => ['containers' => [[
            'name' => 'nginx',
            'image' => 'nginx:alpine',
            'ports' => [['containerPort' => 80]],
        ]]],
    ];
    $run('replication controller repository', function () use ($client, $suffix, $createResource): void {
        $createResource($client, 'replicationControllers', new ReplicationController([
            'metadata' => ['name' => 'rc-' . $suffix],
            'spec' => [
                'replicas' => 1,
                'selector' => ['workload' => 'rc-' . $suffix],
                'template' => [
                    'metadata' => ['labels' => ['workload' => 'rc-' . $suffix]],
                    'spec' => ['containers' => [['name' => 'nginx', 'image' => 'nginx:alpine']]],
                ],
            ],
        ]), 'ReplicationController');
    });
    $run('replica set repository', function () use ($client, $suffix, $selector, $template, $createResource): void {
        $createResource($client, 'replicaSets', new ReplicaSet([
            'metadata' => ['name' => 'rs-' . $suffix],
            'spec' => ['replicas' => 1, 'selector' => $selector, 'template' => $template],
        ]), 'ReplicaSet');
    });
    $deploymentName = 'deployment-' . $suffix;
    $deploymentLabels = ['deployment-suite' => $suffix];
    $run('deployment repository', function () use ($client, $deploymentName, $deploymentLabels, $createResource): void {
        $createResource($client, 'deployments', new Deployment([
            'metadata' => ['name' => $deploymentName],
            'spec' => [
                'replicas' => 1,
                'selector' => ['matchLabels' => $deploymentLabels],
                'template' => [
                    'metadata' => ['labels' => $deploymentLabels],
                    'spec' => ['containers' => [['name' => 'nginx', 'image' => 'nginx:alpine']]],
                ],
            ],
        ]), 'Deployment');
    });
    $run('daemon set repository', function () use ($client, $suffix, $createResource): void {
        $labels = ['daemon-suite' => $suffix];
        $createResource($client, 'daemonSets', new DaemonSet([
            'metadata' => ['name' => 'daemon-' . $suffix],
            'spec' => [
                'selector' => ['matchLabels' => $labels],
                'template' => [
                    'metadata' => ['labels' => $labels],
                    'spec' => ['containers' => [['name' => 'nginx', 'image' => 'nginx:alpine']]],
                ],
            ],
        ]), 'DaemonSet');
    });
    $run('job repository', function () use ($client, $suffix, $createResource): void {
        $createResource($client, 'jobs', new Job([
            'metadata' => ['name' => 'job-' . $suffix],
            'spec' => ['template' => ['spec' => [
                'restartPolicy' => 'Never',
                'containers' => [[
                    'name' => 'job',
                    'image' => 'busybox:1.37.0',
                    'command' => ['sh', '-c', 'echo job-ok'],
                ]],
            ]]],
        ]), 'Job');
    });
    $run('cron job repository', function () use ($client, $suffix, $createResource): void {
        $createResource($client, 'cronJobs', new CronJob([
            'metadata' => ['name' => 'cron-' . $suffix],
            'spec' => [
                'schedule' => '0 0 31 2 *',
                'jobTemplate' => ['spec' => ['template' => ['spec' => [
                    'restartPolicy' => 'Never',
                    'containers' => [[
                        'name' => 'job',
                        'image' => 'busybox:1.37.0',
                        'command' => ['true'],
                    ]],
                ]]]],
            ],
        ]), 'CronJob');
    });
    $run('ingress repository', function () use ($client, $suffix, $serviceName, $createResource): void {
        $createResource($client, 'ingresses', new Ingress([
            'metadata' => ['name' => 'ingress-' . $suffix],
            'spec' => ['rules' => [[
                'host' => 'integration.example.test',
                'http' => ['paths' => [[
                    'path' => '/',
                    'pathType' => 'Prefix',
                    'backend' => ['service' => [
                        'name' => $serviceName,
                        'port' => ['number' => 80],
                    ]],
                ]]],
            ]]],
        ]), 'Ingress');
    });
    $run('network policy repository', function () use ($client, $suffix, $createResource): void {
        $createResource($client, 'networkPolicies', new NetworkPolicy([
            'metadata' => ['name' => 'policy-' . $suffix],
            'spec' => ['podSelector' => new stdClass(), 'policyTypes' => ['Ingress']],
        ]), 'NetworkPolicy');
    });
    $run('horizontal pod autoscaler repository', function () use ($client, $suffix, $deploymentName, $createResource): void {
        $createResource($client, 'horizontalPodAutoscalers', new HorizontalPodAutoscaler([
            'metadata' => ['name' => 'hpa-' . $suffix],
            'spec' => [
                'scaleTargetRef' => ['apiVersion' => 'apps/v1', 'kind' => 'Deployment', 'name' => $deploymentName],
                'minReplicas' => 1,
                'maxReplicas' => 2,
                'metrics' => [[
                    'type' => 'Resource',
                    'resource' => [
                        'name' => 'cpu',
                        'target' => ['type' => 'Utilization', 'averageUtilization' => 80],
                    ],
                ]],
            ],
        ]), 'HorizontalPodAutoscaler');
    });

    $persistentVolumeName = 'pv-' . $suffix;
    $run('persistent volume repository', function () use ($client, $suffix, &$persistentVolumeName, $createResource): void {
        $createResource($client, 'persistentVolume', new PersistentVolume([
            'metadata' => ['name' => $persistentVolumeName],
            'spec' => [
                'capacity' => ['storage' => '64Mi'],
                'accessModes' => ['ReadWriteOnce'],
                'persistentVolumeReclaimPolicy' => 'Delete',
                'storageClassName' => '',
                'hostPath' => ['path' => '/tmp/kubernetes-client-' . $suffix, 'type' => 'DirectoryOrCreate'],
            ],
        ]), 'PersistentVolume');
    });
    $run('persistent volume claim repository', function () use ($client, $suffix, $createResource): void {
        $createResource($client, 'persistentVolumeClaims', new PersistentVolumeClaim([
            'metadata' => ['name' => 'claim-' . $suffix],
            'spec' => [
                'accessModes' => ['ReadWriteOnce'],
                'storageClassName' => '',
                'resources' => ['requests' => ['storage' => '32Mi']],
            ],
        ]), 'PersistentVolumeClaim');
    });
    $run('event repository', function () use ($client, $suffix, $namespace, $namespaceResponse, $createResource): void {
        $createResource($client, 'events', new Event([
            'metadata' => ['name' => 'event-' . $suffix],
            'involvedObject' => [
                'apiVersion' => 'v1',
                'kind' => 'Namespace',
                'name' => $namespace,
                'namespace' => $namespace,
                'uid' => $namespaceResponse['metadata']['uid'],
            ],
            'reason' => 'IntegrationTest',
            'message' => 'kubernetes-client integration test',
            'type' => 'Normal',
            'source' => ['component' => 'kubernetes-client'],
            'firstTimestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'lastTimestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'count' => 1,
        ]), 'Event');
    });
    $run('dynamic API discovery', function () use ($client): void {
        $resources = $client->discoverApiResources();
        $topLevel = array_filter($resources, static function (array $resource): bool {
            return strpos($resource['name'], '/') === false;
        });
        if (count($topLevel) < 72) {
            throw new RuntimeException('Expected at least 72 top-level discovered resources.');
        }
    });
    $run('dynamic repositories list every listable API', function () use ($client): void {
        $failures = [];
        foreach ($client->discoverApiResources() as $resource) {
            if (
                strpos($resource['name'], '/') !== false
                || !in_array('list', $resource['verbs'], true)
            ) {
                continue;
            }

            try {
                $method = $resource['name'];
                $client->{$method}($resource['groupVersion'])->find();
            } catch (Throwable $exception) {
                $failures[] = $resource['groupVersion'] . '/' . $resource['name']
                    . ': ' . $exception->getMessage();
            }
        }

        if ($failures) {
            throw new RuntimeException(implode('; ', $failures));
        }
    });
    $run('dynamic third-party CRD repository', function () use ($client, $suffix): void {
        $issuer = $client->resourceByKind('cert-manager.io/v1', 'Issuer')
            ->get('issuer-' . $suffix);
        if (($issuer->toArray()['kind'] ?? null) !== 'Issuer') {
            throw new RuntimeException('Dynamic CRD lookup did not return an Issuer.');
        }
    });
    $run('automatic built-in repository', function () use ($client): void {
        $client->statefulSets()->find();
    });
    $run('automatic third-party CRD repository', function () use ($client, $suffix): void {
        $name = 'cluster-issuer-' . $suffix;
        $repository = $client->clusterIssuers();
        $created = false;
        try {
            $response = $repository->create(new DynamicResource([
                'apiVersion' => 'cert-manager.io/v1',
                'kind' => 'ClusterIssuer',
                'metadata' => ['name' => $name],
                'spec' => ['selfSigned' => new stdClass()],
            ]));
            $created = true;
            if (($response['kind'] ?? null) !== 'ClusterIssuer') {
                throw new RuntimeException('Automatic CRD repository returned the wrong kind.');
            }
        } finally {
            if ($created) {
                $repository->deleteByName($name);
            }
        }
    });
    $run('explicit alternate API version', function () use ($client): void {
        $client->events('events.k8s.io/v1')->find();
    });
    $run('typed repository pod status subresource', function () use ($client, $podName): void {
        $response = $client->pods()->subresource($podName, 'status');
        if (($response['kind'] ?? null) !== 'Pod') {
            throw new RuntimeException('Pod status subresource did not return a Pod.');
        }
    });
    $run('dynamic deployment scale subresource', function () use ($client, $deploymentName): void {
        $response = $client->resources('apps/v1', 'deployments', true)
            ->subresource($deploymentName, 'scale');
        if (($response['kind'] ?? null) !== 'Scale') {
            throw new RuntimeException('Deployment scale subresource did not return a Scale.');
        }
    });
    $run('node repository', function () use ($client): void {
        $nodes = $client->nodes()->find();
        if ($nodes->count() !== 1) {
            throw new RuntimeException('Expected one node, got ' . $nodes->count());
        }
    });
    $run('node proxy', function () use ($client): void {
        $node = $client->nodes()->first();
        $response = $client->nodes()->proxy($node, 'GET', 'healthz');
        if (trim((string) $response) !== 'ok') {
            throw new RuntimeException('Unexpected kubelet health response.');
        }
    });
} finally {
    if ($persistentVolumeName) {
        try {
            $client->persistentVolume()->deleteByName($persistentVolumeName);
        } catch (Throwable $exception) {
            printf("WARN persistent volume cleanup: %s\n", $exception->getMessage());
        }
    }
    $client->setNamespace('default');
    $deleteResponse = $client->namespaces()->deleteByName($namespace);
    if (
        (($deleteResponse['kind'] ?? null) === 'Status' && ($deleteResponse['status'] ?? null) === 'Success')
        || (($deleteResponse['kind'] ?? null) === 'Namespace' && isset($deleteResponse['metadata']['deletionTimestamp']))
    ) {
        printf("PASS namespace cleanup %s\n", $namespace);
    } else {
        printf("WARN namespace cleanup must be checked: %s\n", $namespace);
    }
}

if ($failures) {
    fwrite(STDERR, "\nFailures:\n");
    foreach ($failures as $name => $failure) {
        fwrite(STDERR, sprintf("- %s: %s\n", $name, $failure));
    }
    exit(1);
}

printf("PASS all smoke tests\n");
