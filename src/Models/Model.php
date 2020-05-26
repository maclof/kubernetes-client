<?php namespace Maclof\Kubernetes\Models;

use JsonException;
use InvalidArgumentException;
use Flow\JSONPath\JSONPath;
use Flow\JSONPath\JSONPathException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;
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
	 * @param mixed  $attributes
	 * @param string $attributeType
	 * @throws \InvalidArgumentException
	 */
	public function __construct($attributes = [], $attributeType = 'array')
	{
		if ($attributeType == 'array') {
			if (is_array($attributes)) {
				$this->attributes = $attributes;
			} else {
				throw new InvalidArgumentException('Attributes are not an array.');
			}
		} elseif ($attributeType == 'json') {
			if (!is_string($attributes)) {
				throw new InvalidArgumentException('JSON attributes must be provided as a JSON encoded string.');
			}

			try {
				$this->attributes = json_decode($attributes, true, 512, JSON_THROW_ON_ERROR);
			} catch (JsonException $e) {
				throw new InvalidArgumentException('Failed to parse JSON encoded attributes: ' . $e->getMessage(), 0, $e);
			}
		} elseif ($attributeType == 'yaml') {
			if (!is_string($attributes)) {
				throw new InvalidArgumentException('YAML attributes must be provided as a YAML encoded string.');
			}

			try {
				$this->attributes = Yaml::parse($attributes);
			} catch (YamlParseException $e) {
				throw new InvalidArgumentException('Failed to parse YAML encoded attributes: ' . $e->getMessage(), 0, $e);
			}
		} else {
			throw new InvalidArgumentException('Invalid attribute type: ' . $attributeType);
		}
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
