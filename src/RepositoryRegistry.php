<?php namespace Maclof\Kubernetes;

class RepositoryRegistry implements \ArrayAccess, \Countable
{

    /**
     * @var array Initial registry class map. Contains only package builtin repositories.
     */
    protected $map = [
        'nodes'                  => Repositories\NodeRepository::class,
        'quotas'                 => Repositories\QuotaRepository::class,
        'pods'                   => Repositories\PodRepository::class,
        'replicaSets'            => Repositories\ReplicaSetRepository::class,
        'replicationControllers' => Repositories\ReplicationControllerRepository::class,
        'services'               => Repositories\ServiceRepository::class,
        'secrets'                => Repositories\SecretRepository::class,
        'events'                 => Repositories\EventRepository::class,
        'configMaps'             => Repositories\ConfigMapRepository::class,
        'endpoints'              => Repositories\EndpointRepository::class,
        'persistentVolume'       => Repositories\PersistentVolumeRepository::class,
        'persistentVolumeClaims' => Repositories\PersistentVolumeClaimRepository::class,
        'namespaces'             => Repositories\NamespaceRepository::class,

        // batch/v1
        'jobs'                   => Repositories\JobRepository::class,

        // batch/v2alpha1
        'cronJobs'               => Repositories\CronJobRepository::class,

        // apps/v1
        'deployments'            => Repositories\DeploymentRepository::class,

        // extensions/v1beta1
        'daemonSets'             => Repositories\DaemonSetRepository::class,
        'ingresses'              => Repositories\IngressRepository::class,

        // autoscaling/v2beta1
        'horizontalPodAutoscalers'  => Repositories\HorizontalPodAutoscalerRepository::class,

        // networking.k8s.io/v1
        'networkPolicies'        => Repositories\NetworkPolicyRepository::class,

        // certmanager.k8s.io/v1alpha1
        'certificates'           => Repositories\CertificateRepository::class,
        'issuers'                => Repositories\IssuerRepository::class,

    ];

    public function __construct()
    {

    }

	#[\ReturnTypeWillChange]
    public function offsetExists($method)
    {
        return isset($this->map[$method]);
    }

	#[\ReturnTypeWillChange]
    public function offsetGet($method)
    {
        return $this->map[$method];
    }

	#[\ReturnTypeWillChange]
    public function offsetSet($method, $class)
    {
        $this->map[$method] = $class;
    }

    public function offsetUnset($method)
    {
        unset($this->map[$method]);
    }

	#[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->map);
    }
}
