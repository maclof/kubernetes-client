<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Client;
use Maclof\Kubernetes\Collections\Collection;
use Maclof\Kubernetes\Collections\DynamicResourceCollection;
use Maclof\Kubernetes\Models\Model;

/**
 * Repository for discovered built-in resources and third-party CRDs.
 */
class DynamicRepository extends Repository
{
	protected array $definition;

	public function __construct(
		Client $client,
		string $apiVersion,
		string $resource,
		bool $namespaced,
		array $definition = []
	) {
		parent::__construct($client);
		$this->apiVersion = $apiVersion;
		$this->uri = trim($resource, '/');
		$this->namespace = $namespaced;
		$this->definition = $definition;
	}

	public function getResourceName(): string
	{
		return $this->uri;
	}

	public function isNamespaced(): bool
	{
		return $this->namespace;
	}

	public function getDefinition(): array
	{
		return $this->definition;
	}

	public function patch(Model $model): array
	{
		$patchType = $this->definition['patchType'] ?? 'merge';
		$this->client->setPatchType($patchType);
		try {
			return parent::patch($model);
		} finally {
			$this->client->setPatchType();
		}
	}

	protected function createCollection($response): Collection
	{
		if (isset($this->definition['collection'])) {
			$collection = $this->definition['collection'];

			return new $collection($response['items']);
		}

		return new DynamicResourceCollection($response['items']);
	}
}
