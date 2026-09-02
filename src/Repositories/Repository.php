<?php namespace Maclof\Kubernetes\Repositories;

use Closure;
use InvalidArgumentException;
use Maclof\Kubernetes\Client;
use Maclof\Kubernetes\Collections\Collection;
use Maclof\Kubernetes\Models\Model;
use Maclof\Kubernetes\Models\DeleteOptions;
use Maclof\Kubernetes\Repositories\Utils\JSONStreamingParser;
use Maclof\Kubernetes\Repositories\Utils\JSONStreamingListener;

abstract class Repository
{
	/**
	 * The client.
	 */
	protected Client $client;

	/**
	 * The resource uri, has to be overwritten.
	 */
	protected string $uri = '';

	/**
	 * Include the namespace in the requests.
	 */
	protected bool $namespace = true;

	/**
	 * The api version to use for requests.
	 */
	protected ?string $apiVersion = null;

	/**
	 * The label selector.
	 */
	protected array $labelSelector = [];

	/**
	 * The label selector options that should not match.
	 */
	protected array $inequalityLabelSelector = [];

	/**
	 * The field selector.
	 */
	protected array $fieldSelector = [];

	/**
	 * The field selector options that should not match.
	 */
	protected array $inequalityFieldSelector = [];

	/**
	 * The default class namespace of the repositories
	 */
	protected string $modelClassNamespace = 'Maclof\Kubernetes\Models\\';

	/**
	 * The constructor.
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * Send a request.
	 *
	 * @param mixed $body
	 * @return mixed
	 */
	protected function sendRequest(string $method, string $uri, array $query = [], $body = [], bool $namespace = true, array $requestOptions = [])
	{
		$apiVersion = $this->getApiVersion();
		if ($apiVersion === 'v1') {
			$apiVersion = null;
		}

		return $this->client->sendRequest($method, $uri, $query, $body, $namespace, $apiVersion, $requestOptions);
	}

	/**
	 * Get the api version from the model.
	 */
	protected function getApiVersion(): ?string
	{
		if ($this->apiVersion) {
			return $this->apiVersion;
		}

		$className = str_replace('Repository', '', class_basename($this));
		$classPath = $this->modelClassNamespace . $className;

		if (!class_exists($classPath)) {
			return null;
		}

		$this->apiVersion = (new $classPath)->getApiVersion();

		return $this->apiVersion;
	}

	/**
	 * Create a new model.
	 */
	public function create(Model $model): array
	{
		return $this->sendRequest('POST', '/' . $this->uri, [], $model->getSchema(), $this->namespace);
	}

	/**
	 * Get a model directly by name.
	 */
	public function get(string $name): Model
	{
		$response = $this->sendRequest(
			'GET',
			'/' . $this->uri . '/' . rawurlencode($name),
			[],
			null,
			$this->namespace
		);
		$model = $this->createCollection(['items' => [$response]])->first();
		if (!$model) {
			throw new \UnexpectedValueException('The API response did not contain a resource.');
		}

		return $model;
	}

	/**
	 * Update a model.
	 */
	public function update(Model $model): array
	{
		return $this->sendRequest('PUT', '/' . $this->uri . '/' . $model->getMetadata('name'), [], $model->getSchema(), $this->namespace);
	}

	/**
	 * Patch a model.
	 */
	public function patch(Model $model): array
	{
		return $this->sendRequest('PATCH', '/' . $this->uri . '/' . $model->getMetadata('name'), [], $model->getSchema(), $this->namespace);
	}

	/**
	 * Apply a json patch to a model.
	 */
	public function applyJsonPatch(Model $model, array $patch): array
	{
		$patch = json_encode($patch);

		$this->client->setPatchType('json');
		try {
			return $this->sendRequest('PATCH', '/' . $this->uri . '/' . $model->getMetadata('name'), [], $patch, $this->namespace);
		} finally {
			$this->client->setPatchType();
		}
	}

    /**
     * Apply a model.
     *
     * Creates a new api object if not exists, or patch.
     */
	public function apply(Model $model): array
    {
        $exists = $this->exists((string)$model->getMetadata("name"));

        if ($exists) {
            return $this->patch($model);
        } else {
            return $this->create($model);
        }
    }

	/**
	 * Delete a model.
	 */
	public function delete(Model $model, ?DeleteOptions $options = null): array
	{
		return $this->deleteByName((string)$model->getMetadata('name'), $options);
	}

	/**
	 * Delete a model by name.
	 */
	public function deleteByName(string $name, ?DeleteOptions $options = null): array
	{
		$body = $options ? $options->getSchema() : null;

		return $this->sendRequest('DELETE', '/' . $this->uri . '/' . $name, [], $body, $this->namespace);
	}

	/**
	 * Delete every resource matching the optional selectors/query.
	 */
	public function deleteCollection(array $query = [], ?DeleteOptions $options = null): array
	{
		$body = $options ? $options->getSchema() : null;

		return $this->sendRequest('DELETE', '/' . $this->uri, $query, $body, $this->namespace);
	}

	/**
	 * Request any named subresource, including CRD status/scale and proxy paths.
	 *
	 * @param mixed $body
	 * @return mixed
	 */
	public function subresource(
		string $name,
		string $subresource,
		string $method = 'GET',
		$body = null,
		array $query = [],
		array $requestOptions = []
	) {
		$uri = '/' . $this->uri . '/' . rawurlencode($name) . '/' . ltrim($subresource, '/');

		return $this->sendRequest(
			strtoupper($method),
			$uri,
			$query,
			$body,
			$this->namespace,
			$requestOptions
		);
	}

	/**
	 * Patch a named subresource with the requested patch media type.
	 *
	 * @param mixed $body
	 * @return mixed
	 */
	public function patchSubresource(
		string $name,
		string $subresource,
		$body,
		string $patchType = 'merge',
		array $query = []
	) {
		$this->client->setPatchType($patchType);
		try {
			return $this->subresource($name, $subresource, 'PATCH', $body, $query);
		} finally {
			$this->client->setPatchType();
		}
	}

	/**
	 * Get logs from a resource exposing a log subresource.
	 *
	 * @param Model|string $resource
	 */
	public function logs($resource, array $query = []): string
	{
		return $this->subresource(
			$this->resolveResourceName($resource),
			'log',
			'GET',
			null,
			$query,
			['decode_json' => false]
		);
	}

	/**
	 * Execute a command through a resource's exec subresource.
	 *
	 * @param Model|string $resource
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function exec($resource, array $query = [])
	{
		return $this->subresource(
			$this->resolveResourceName($resource),
			'exec',
			'POST',
			null,
			$query
		);
	}

	/**
	 * Attach an ephemeral container through the ephemeralcontainers subresource.
	 *
	 * @param Model|string $resource
	 */
	public function debug($resource, array $spec): array
	{
		$this->client->setPatchType('strategic');
		try {
			return $this->subresource(
				$this->resolveResourceName($resource),
				'ephemeralcontainers',
				'PATCH',
				json_encode($spec)
			);
		} finally {
			$this->client->setPatchType();
		}
	}

	/**
	 * Request a path through a resource's proxy subresource.
	 *
	 * @param Model|string $resource
	 * @return mixed
	 */
	public function proxy($resource, string $method, string $path, array $query = [])
	{
		return $this->subresource(
			$this->resolveResourceName($resource),
			'proxy/' . ltrim($path, '/'),
			$method,
			null,
			$query
		);
	}

	/**
	 * @param Model|string $resource
	 */
	protected function resolveResourceName($resource): string
	{
		if ($resource instanceof Model) {
			$name = $resource->getMetadata('name');
		} elseif (is_string($resource)) {
			$name = $resource;
		} else {
			throw new InvalidArgumentException('Resource must be a model or resource name.');
		}

		if (!is_string($name) || $name === '') {
			throw new InvalidArgumentException('Resource name is missing.');
		}

		return $name;
	}

	/**
	 * Set the label selector including inequality search terms.
	 */
	public function setLabelSelector(array $labelSelector, array $inequalityLabelSelector=[]): Repository
	{
		$this->labelSelector           = $labelSelector;
		$this->inequalityLabelSelector = $inequalityLabelSelector;
		return $this;
	}

	/**
	 * Get the label selector query.
	 */
	protected function getLabelSelectorQuery(): string
	{
		$parts = [];
		foreach ($this->labelSelector as $key => $value) {
			$parts[] = null === $value ? $key : ($key . '=' . $value);
		}

		// If any inequality search terms are set, add them to the parts array
		if (!empty($this->inequalityLabelSelector)) {
			foreach ($this->inequalityLabelSelector as $key => $value) {
				$parts[] = $key . '!=' . $value;
			}
		}
		return implode(',', $parts);
	}

	/**
	 * Set the field selector including inequality search terms.
	 */
	public function setFieldSelector(array $fieldSelector, array $inequalityFieldSelector=[]): Repository
	{
		$this->fieldSelector           = $fieldSelector;
		$this->inequalityFieldSelector = $inequalityFieldSelector;
		return $this;
	}

	/**
	 * Get the field selector query.
	 */
	protected function getFieldSelectorQuery(): string
	{
		$parts = [];
		foreach ($this->fieldSelector as $key => $value) {
			$parts[] = $key . '=' . $value;
		}

		// If any inequality search terms are set, add them to the parts array
		if (!empty($this->inequalityFieldSelector)) {
			foreach ($this->inequalityFieldSelector as $key => $value) {
				$parts[] = $key . '!=' . $value;
			}
		}

		return implode(',', $parts);
	}

	/**
	 * Reset the parameters.
	 */
	protected function resetParameters(): void
	{
		$this->labelSelector = [];
		$this->fieldSelector = [];
	}

	/**
	 * Get a collection of items.
	 */
	public function find(array $query = []): Collection
	{
		$query = array_filter(array_merge([
			'labelSelector' => $this->getLabelSelectorQuery(),
			'fieldSelector' => $this->getFieldSelectorQuery(),
		], $query), function ($value) {
			return !is_null($value) && strlen($value) > 0;
		});

		$this->resetParameters();

		$response = $this->sendRequest('GET', '/' . $this->uri, $query, null, $this->namespace);

		return $this->createCollection($response);
	}

	/**
	 * Find the first item.
	 */
	public function first(): ?Model
	{
		return $this->find()->first();
	}

	/**
	 * Watch a model for changes.
	 */
	public function watch(Model $model, Closure $closure, array $query = []): void
	{
		$this->setFieldSelector([
			'metadata.name' => $model->getMetadata('name'),
		]);

		$query = array_filter(array_merge([
			'watch'          => true,
			'timeoutSeconds' => 30,
			'labelSelector'  => $this->getLabelSelectorQuery(),
			'fieldSelector'  => $this->getFieldSelectorQuery(),
		], $query), function ($value) {
			return !is_null($value) && strlen($value) > 0;
		});

		$this->resetParameters();

		$response = $this->sendRequest('GET', '/' . $this->uri, $query, null, $this->namespace, [
			'stream'       => true,
			'read_timeout' => 5,
		]);

		$stream = $response->getBody();

		$parser = new JSONStreamingParser($stream, new JSONStreamingListener($closure));
		$parser->parse();
	}

	/**
	 * Check if an item exists by name.
	 */
	public function exists(string $name): bool
	{
		$this->resetParameters();
		return !is_null($this->setFieldSelector(['metadata.name' => $name])->first());
	}

	/**
	 * Create a collection of models from the response.
	 */
	abstract protected function createCollection(array $response): Collection;
}
