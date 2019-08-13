<?php namespace Maclof\Kubernetes\Models;

use Flow\JSONPath\JSONPath;
use Flow\JSONPath\JSONPathException;
use Illuminate\Contracts\Support\Arrayable;

abstract class Model implements Arrayable
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
     * Get data ussing Json Path.
     *
     * @param  string $expression
     * @throws JSONPathException
     * @return mixed
     */
    public function getJsonPath($expression)
    {
        $jsonPath = new JSONPath($this->attributes);

        return $jsonPath->find($expression);
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

		$jsonSchema = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

		// Fix for issue #37, can't use JSON_FORCE_OBJECT as the encoding breaks arrays of objects, for example port mappings.
		$jsonSchema = str_replace(': []', ': {}', $jsonSchema);

		return $jsonSchema;
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
	 * Set the api version.
	 * 
	 * @param string $apiVersion
	 */
	public function setApiVersion($apiVersion)
	{
		$this->apiVersion = $apiVersion;
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
