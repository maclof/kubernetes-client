<?php namespace Maclof\Kubernetes\Models;

abstract class Model
{
	/**
	 * The schema.
	 *
	 * @var array
	 */
	protected $schema = [];

	/**
	 * The api version.
	 *
	 * @var string
	 */
	protected $apiVersion = 'v1';

	/**
	 * Whether or not the kind is plural.
	 *
	 * @var boolean
	 */
	protected $pluralKind = false;

	/**
	 * The attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * The constructor.
	 *
	 * @param array $attributes
	 */
	public function __construct(array $attributes = array())
	{
		$this->attributes = $attributes;
	}

	/**
	 * Get the model as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->attributes;
	}

	/**
	 * Get some metadata.
	 *
	 * @param  string $key
	 * @return string
	 */
	public function getMetadata($key)
	{
		return isset($this->attributes['metadata'][$key]) ? $this->attributes['metadata'][$key] : null;
	}

	/**
	 * Get the schema.
	 *
	 * @return string
	 */
	public function getSchema()
	{
		if (!isset($this->schema['kind'])) {
			$this->schema['kind'] = basename(str_replace('\\', '/', get_class($this)));
			if ($this->pluralKind) {
				$this->schema['kind'] .= 's';
			}
		}

		if (!isset($this->schema['apiVersion'])) {
			$this->schema['apiVersion'] = $this->apiVersion;
		}

		$schema = array_merge($this->schema, $this->toArray());

		return json_encode($schema, JSON_PRETTY_PRINT);
	}

	/**
	 * Get the api version.
	 *
	 * @return string
	 */
	public function getApiVersion()
	{
		return $this->apiVersion;
	}

	/**
	 * Get the model as a string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getSchema();
	}
}
