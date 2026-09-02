<?php namespace Maclof\Kubernetes;

use Maclof\Kubernetes\Collections;

class RepositoryRegistry implements \ArrayAccess, \Countable
{
	/**
	 * Backward-compatible method aliases backed by the universal repository.
	 */
	protected array $map = [
		'nodes' => ['apiVersion' => 'v1', 'resource' => 'nodes', 'namespaced' => false, 'collection' => Collections\NodeCollection::class],
		'quotas' => ['apiVersion' => 'v1', 'resource' => 'resourcequotas', 'namespaced' => true, 'collection' => Collections\QuotaCollection::class],
		'pods' => ['apiVersion' => 'v1', 'resource' => 'pods', 'namespaced' => true, 'collection' => Collections\PodCollection::class],
		'replicaSets' => ['apiVersion' => 'apps/v1', 'resource' => 'replicasets', 'namespaced' => true, 'collection' => Collections\ReplicaSetCollection::class],
		'replicationControllers' => ['apiVersion' => 'v1', 'resource' => 'replicationcontrollers', 'namespaced' => true, 'collection' => Collections\ReplicationControllerCollection::class],
		'services' => ['apiVersion' => 'v1', 'resource' => 'services', 'namespaced' => true, 'collection' => Collections\ServiceCollection::class],
		'secrets' => ['apiVersion' => 'v1', 'resource' => 'secrets', 'namespaced' => true, 'collection' => Collections\SecretCollection::class],
		'events' => ['apiVersion' => 'v1', 'resource' => 'events', 'namespaced' => true, 'collection' => Collections\EventCollection::class],
		'configMaps' => ['apiVersion' => 'v1', 'resource' => 'configmaps', 'namespaced' => true, 'collection' => Collections\ConfigMapCollection::class],
		'endpoints' => ['apiVersion' => 'v1', 'resource' => 'endpoints', 'namespaced' => true, 'collection' => Collections\EndpointCollection::class],
		'persistentVolume' => ['apiVersion' => 'v1', 'resource' => 'persistentvolumes', 'namespaced' => false, 'collection' => Collections\PersistentVolumeCollection::class],
		'persistentVolumeClaims' => ['apiVersion' => 'v1', 'resource' => 'persistentvolumeclaims', 'namespaced' => true, 'collection' => Collections\PersistentVolumeClaimCollection::class],
		'namespaces' => ['apiVersion' => 'v1', 'resource' => 'namespaces', 'namespaced' => false, 'collection' => Collections\NamespaceCollection::class],
		'serviceAccounts' => ['apiVersion' => 'v1', 'resource' => 'serviceaccounts', 'namespaced' => true, 'collection' => Collections\ServiceAccountCollection::class],
		'jobs' => ['apiVersion' => 'batch/v1', 'resource' => 'jobs', 'namespaced' => true, 'collection' => Collections\JobCollection::class],
		'cronJobs' => ['apiVersion' => 'batch/v1', 'resource' => 'cronjobs', 'namespaced' => true, 'collection' => Collections\CronJobCollection::class],
		'deployments' => ['apiVersion' => 'apps/v1', 'resource' => 'deployments', 'namespaced' => true, 'collection' => Collections\DeploymentCollection::class],
		'daemonSets' => ['apiVersion' => 'apps/v1', 'resource' => 'daemonsets', 'namespaced' => true, 'collection' => Collections\DaemonSetCollection::class],
		'ingresses' => ['apiVersion' => 'networking.k8s.io/v1', 'resource' => 'ingresses', 'namespaced' => true, 'collection' => Collections\IngressCollection::class],
		'horizontalPodAutoscalers' => ['apiVersion' => 'autoscaling/v2', 'resource' => 'horizontalpodautoscalers', 'namespaced' => true, 'collection' => Collections\HorizontalPodAutoscalerCollection::class],
		'networkPolicies' => ['apiVersion' => 'networking.k8s.io/v1', 'resource' => 'networkpolicies', 'namespaced' => true, 'collection' => Collections\NetworkPolicyCollection::class],
		'certificates' => ['apiVersion' => 'cert-manager.io/v1', 'resource' => 'certificates', 'namespaced' => true, 'collection' => Collections\CertificateCollection::class, 'patchType' => 'merge'],
		'issuers' => ['apiVersion' => 'cert-manager.io/v1', 'resource' => 'issuers', 'namespaced' => true, 'collection' => Collections\IssuerCollection::class, 'patchType' => 'merge'],
		'roles' => ['apiVersion' => 'rbac.authorization.k8s.io/v1', 'resource' => 'roles', 'namespaced' => true, 'collection' => Collections\RoleCollection::class],
		'roleBindings' => ['apiVersion' => 'rbac.authorization.k8s.io/v1', 'resource' => 'rolebindings', 'namespaced' => true, 'collection' => Collections\RoleBindingCollection::class],
	];

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
