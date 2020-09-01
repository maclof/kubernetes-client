<?php namespace Maclof\Kubernetes\Repositories;

use Closure;
use Maclof\Kubernetes\Models\Model;
use Maclof\Kubernetes\Models\DeleteOptions;
use Maclof\Kubernetes\Repositories\Utils\JSONStreamingParser;
use Maclof\Kubernetes\Repositories\Utils\JSONStreamingListener;

abstract class Repository
{
	/**
	 * The client.
	 *
	 * @var \Maclof\Kubernetes\Client
	 */
	protected $client;

	/**
	 * Include the namespace in the requests.
	 *
	 * @var boolean
	 */
	protected $namespace = true;

	/**
	 * The api version to use for requests.
	 *
	 * @var null
	 */
	protected $apiVersion;

	/**
	 * The label selector.
	 *
	 * @var array
	 */
	protected $labelSelector = [];

	/**
	 * The field selector.
	 *
	 * @var array
	 */
	protected $fieldSelector = [];


	/**
	 * The default class namespace of the repositories
	 * 
	 * @var string
	 */
	protected $modelClassNamespace = 'Maclof\Kubernetes\Models\\';

	/**
	 * The constructor.
	 *
	 * @param \Maclof\Kubernetes\Client $client
	 */
	public function __construct($client)
	{
		$this->client = $client;
	}

	/**
	 * Send a request.
	 *
	 * @param  string  $method
	 * @param  string  $uri
	 * @param  array   $query
	 * @param  mixed   $body
	 * @param  boolean $namespace
	 * @param  array   $requestOptions
	 * @return array
	 */
	protected function sendRequest($method, $uri, $query = [], $body = [], $namespace = true, array $requestOptions = [])
	{
		$apiVersion = $this->getApiVersion();
		if ($apiVersion == 'v1') {
			$apiVersion = null;
		}

		return $this->client->sendRequest($method, $uri, $query, $body, $namespace, $apiVersion, $requestOptions);
	}

	/**
	 * Get the api version from the model.
	 *
	 * @return string
	 */
	protected function getApiVersion()
	{
		if ($this->apiVersion) {
			return $this->apiVersion;
		}

		$className = str_replace('Repository', '', class_basename($this));
		$classPath = $this->modelClassNamespace . $className;

		if (!class_exists($classPath)) {
			return;
		}

		$this->apiVersion = (new $classPath)->getApiVersion();

		return $this->apiVersion;
	}

	/**
	 * Create a new model.
	 *
	 * @param  \Maclof\Kubernetes\Models\Model $model
	 * @return array
	 */
	public function create(Model $model)
	{
		return $this->sendRequest('POST', '/' . $this->uri, null, $model->getSchema(), $this->namespace);
	}

	/**
	 * Update a model.
	 *
	 * @param  \Maclof\Kubernetes\Models\Model $model
	 * @return array
	 */
	public function update(Model $model)
	{
		return $this->sendRequest('PUT', '/' . $this->uri . '/' . $model->getMetadata('name'), null, $model->getSchema(), $this->namespace);
	}

	/**
	 * Patch a model.
	 *
	 * @param \Maclof\Kubernetes\Models\Model $model
	 * @return array
	 */
	public function patch(Model $model)
	{
		return $this->sendRequest('PATCH', '/' . $this->uri . '/' . $model->getMetadata('name'), null, $model->getSchema(), $this->namespace);
	}

    /**
     * Apply a model.
     *
     * Creates a new api object if not exists, or patch.
     *
     * @param \Maclof\Kubernetes\Models\Model $model
     * @return array
     */
	public function apply(Model $model)
    {
        $exists = $this->exists($model->getMetadata("name"));

        if ($exists) {
            return $this->patch($model);
        } else {
            return $this->create($model);
        }
    }

	/**
	 * Delete a model.
	 *
	 * @param  \Maclof\Kubernetes\Models\Model         $model
	 * @param  \Maclof\Kubernetes\Models\DeleteOptions $options
	 * @return array
	 */
	public function delete(Model $model, DeleteOptions $options = null)
	{
		return $this->deleteByName($model->getMetadata('name'), $options);
	}

	/**
	 * Delete a model by name.
	 *
	 * @param  string                                  $name
	 * @param  \Maclof\Kubernetes\Models\DeleteOptions $options
	 * @return array
	 */
	public function deleteByName($name, DeleteOptions $options = null)
	{
		$body = $options ? $options->getSchema() : null;

		return $this->sendRequest('DELETE', '/' . $this->uri . '/' . $name, null, $body, $this->namespace);
	}

	/**
	 * Set the label selector.
	 *
	 * @param  array $labelSelector
	 * @return \Maclof\Kubernetes\Repositories\Repository
	 */
	public function setLabelSelector(array $labelSelector)
	{
		$this->labelSelector = $labelSelector;
		return $this;
	}

	/**
	 * Get the label selector query.
	 *
	 * @return string
	 */
	protected function getLabelSelectorQuery()
	{
		$parts = [];
		foreach ($this->labelSelector as $key => $value) {
			$parts[] = $key . '=' . $value;
		}
		return implode(',', $parts);
	}

	/**
	 * Set the field selector.
	 *
	 * @param  array $fieldSelector
	 * @return \Maclof\Kubernetes\Repositories\Repository
	 */
	public function setFieldSelector(array $fieldSelector)
	{
		$this->fieldSelector = $fieldSelector;
		return $this;
	}

	/**
	 * Get the field selector query.
	 *
	 * @return string
	 */
	protected function getFieldSelectorQuery()
	{
		$parts = [];
		foreach ($this->fieldSelector as $key => $value) {
			$parts[] = $key . '=' . $value;
		}
		return implode(',', $parts);
	}

	/**
	 * Reset the parameters.
	 *
	 * @return void
	 */
	protected function resetParameters()
	{
		$this->labelSelector = [];
		$this->fieldSelector = [];
	}

	/**
	 * Get a collection of items.
	 *
	 * @param  array $query
	 * @return mixed
	 */
	public function find(array $query = [])
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
	 *
	 * @return \Maclof\Kubernetes\Models\Model|null
	 */
	public function first()
	{
		return $this->find()->first();
	}

	/**
	 * Watch a model for changes.
	 *
	 * @param  \Maclof\Kubernetes\Models\Model $model
	 * @param  \Closure $closure
	 * @param  array $query
	 * @return void
	 */
	public function watch(Model $model, Closure $closure, array $query = [])
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
	 *
	 * @param  string $name
	 * @return boolean
	 */
	public function exists($name)
	{
		$this->resetParameters();
		return !is_null($this->setFieldSelector(['metadata.name' => $name])->first());
	}

	/**
	 * Create a collection of models from the response.
	 *
	 * @param  array $response
	 * @return \Maclof\Kubernetes\Collections\Collection
	 */
	abstract protected function createCollection($response);
}
