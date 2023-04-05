<?php namespace Maclof\Kubernetes;

class RepositoryRegistry implements \ArrayAccess, \Countable
{

    /**
     * Initial registry class map. Contains only package builtin repositories.
     */
    protected array $map = [
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
		'serviceAccounts'	     => Repositories\ServiceAccountRepository::class,

        // batch/v1
        'jobs'                   => Repositories\JobRepository::class,

        // batch/v2
        'cronJobs'               => Repositories\CronJobRepository::class,

        // apps/v1
        'deployments'            => Repositories\DeploymentRepository::class,

        // extensions/v1
        'daemonSets'             => Repositories\DaemonSetRepository::class,
        'ingresses'              => Repositories\IngressRepository::class,

        // autoscaling/v2
        'horizontalPodAutoscalers'  => Repositories\HorizontalPodAutoscalerRepository::class,

        // networking.k8s.io/v1
        'networkPolicies'        => Repositories\NetworkPolicyRepository::class,

        // certmanager.k8s.io/v1
        'certificates'           => Repositories\CertificateRepository::class,
        'issuers'                => Repositories\IssuerRepository::class,

		//rbac.authorization.k8s.io/v1
		'roles' 				 => Repositories\RoleRepository::class,
		'roleBindings' 			 => Repositories\RoleBindingRepository::class,
		'clusterRoles' 				 => Repositories\ClusterRoleRepository::class,
		'clusterRoleBindings' 			 => Repositories\ClusterRoleBindingRepository::class,

        //hnc.x-k8s.io/v1
		'subnamespacesAnchors'   => Repositories\SubnamespaceAnchorRepository::class,
    ];

    public function __construct()
    {

    }

    public function offsetExists($offset): bool
    {
        return isset($this->map[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->map[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->map[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->map[$offset]);
    }

    public function count(): int
    {
        return count($this->map);
    }
}
