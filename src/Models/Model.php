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
	 */
	protected array $schema = [];

	/**
	 * The api version.
	 */
	protected string $apiVersion = 'v1';

	/**
	 * Whether or not the kind is plural.
	 */
	protected bool $pluralKind = false;

	/**
	 * The attributes.
	 */
	protected array $attributes = [];

	/**
	 * The constructor.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $attributes = [], string $attributeType = 'array')
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
	 */
	public function toArray(): array
	{
		return $this->attributes;
	}

	/**
	 * Get some metadata.
	 */
	public function getMetadata(string $key)
	{
		return isset($this->attributes['metadata'][$key]) ? $this->attributes['metadata'][$key] : null;
	}

	/**
     * Get data ussing Json Path.
     *
     * @throws JSONPathException
     */
    public function getJsonPath(string $expression): JSONPath
    {
        $jsonPath = new JSONPath($this->attributes);

        return $jsonPath->find($expression);
    }

	 /**
	 * Get the schema.
	 */
	public function getSchema(): string
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
	 */
	public function getApiVersion(): string
	{
		return $this->apiVersion;
	}

	/**
	 * Set the api version.
	 */
	public function setApiVersion(string $apiVersion): void
	{
		$this->apiVersion = $apiVersion;
	}

	/**
	 * Get the model as a string.
	 */
	public function __toString()
	{
		return $this->getSchema();
	}
}
