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
	protected $apiVersion = 'v1beta3';

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
	 * Get the schema.
	 * 
	 * @return string
	 */
	public function getSchema()
	{
		$this->schema['kind'] = basename(str_replace('\\', '/', get_class($this)));
		$this->schema['apiVersion'] = $this->apiVersion;

		$schema = array_merge($this->schema, $this->toArray());

		return json_encode($schema, JSON_PRETTY_PRINT);
	}

	/**
	 * Get some metadata.
	 *
	 * @param  string $key
	 * @return string
	 */
	public function getMetadata($key)
	{
		return $this->attributes['metadata'][$key];
	}
}