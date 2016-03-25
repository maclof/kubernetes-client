<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Models\Model;

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
	 * Send the request to the beta endpoint.
	 *
	 * @var boolean
	 */
	protected $beta = false;

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
	 * The constructor.
	 *
	 * @param \Maclof\Kubernetes\Client $client
	 */
	public function __construct($client)
	{
		$this->client = $client;
	}

	/**
	 * Create a new model.
	 *
	 * @param  \Maclof\Kubernetes\Models\Model $model
	 * @return boolean
	 */
	public function create(Model $model)
	{
		if ($this->beta) {
			$this->client->sendBetaRequest('POST', '/' . $this->uri, null, $model->getSchema());
		} else {
			$this->client->sendRequest('POST', '/' . $this->uri, null, $model->getSchema());
		}
		return true;
	}

	/**
	 * Delete a model.
	 *
	 * @param  \Maclof\Kubernetes\Models\Model $model
	 * @return boolean
	 */
	public function delete(Model $model)
	{
		return $this->deleteByName($model->getMetadata('name'));
	}

	/**
	 * Delete a model by name.
	 *
	 * @param  string $name
	 * @return boolean
	 */
	public function deleteByName($name)
	{
		if ($this->beta) {
			$this->client->sendBetaRequest('DELETE', '/' . $this->uri . '/' . $name);
		} else {
			$this->client->sendRequest('DELETE', '/' . $this->uri . '/' . $name);
		}
		return true;
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
	 * @return \Maclof\Kubernetes\Collections\Collection
	 */
	public function find()
	{
		$query = array_filter([
			'labelSelector' => $this->getLabelSelectorQuery(),
			'fieldSelector' => $this->getFieldSelectorQuery(),
		]);

		if ($this->beta) {
			$response = $this->client->sendBetaRequest('GET', '/' . $this->uri, $query, null, $this->namespace);
		} else {
			$response = $this->client->sendRequest('GET', '/' . $this->uri, $query, null, $this->namespace);
		}

		$this->resetParameters();

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

	abstract protected function createCollection($response);
}
