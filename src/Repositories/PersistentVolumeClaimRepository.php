<?php namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\PersistentVolumeClaimCollection;

class PersistentVolumeClaimRepository extends Repository
{
	protected string $uri = 'persistentvolumeclaims';

	protected function createCollection($response): PersistentVolumeClaimCollection
	{
		return new PersistentVolumeClaimCollection($response['items']);
	}
}
