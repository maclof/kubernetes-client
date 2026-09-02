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
	
	public const MODEL_FROM_ARRAY = 'array';

	public const MODEL_FROM_JSON = 'json';

	public const MODEL_FROM_YAML = 'yaml';

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
	 * @param array|string $attributes
	 * @throws \InvalidArgumentException
	 */
	public function __construct($attributes = [], string $attributeType = self::MODEL_FROM_ARRAY)
	{
		if ($attributeType == self::MODEL_FROM_ARRAY) {
			if (is_array($attributes)) {
				$this->attributes = $attributes;
			} else {
				throw new InvalidArgumentException('Attributes are not an array.');
			}
		} elseif ($attributeType == self::MODEL_FROM_JSON) {
			$encoded = is_string($attributes)
				? $attributes
				: ($attributes[self::MODEL_FROM_JSON] ?? null);
			if (!is_string($encoded)) {
				throw new InvalidArgumentException('JSON attributes must be provided as a JSON encoded string.');
			}

			try {
				$decoded = json_decode($encoded, true, 512, JSON_THROW_ON_ERROR);
				if (!is_array($decoded)) {
					throw new InvalidArgumentException('JSON attributes must decode to an object or array.');
				}
				$this->attributes = is_string($attributes)
					? $decoded
					: [self::MODEL_FROM_JSON => $decoded];
			} catch (JsonException $e) {
				throw new InvalidArgumentException('Failed to parse JSON encoded attributes: ' . $e->getMessage(), 0, $e);
			}
		} elseif ($attributeType == self::MODEL_FROM_YAML) {
			$encoded = is_string($attributes)
				? $attributes
				: ($attributes[self::MODEL_FROM_YAML] ?? null);
			if (!is_string($encoded)) {
				throw new InvalidArgumentException('YAML attributes must be provided as a YAML encoded string.');
			}

			try {
				$decoded = Yaml::parse($encoded);
				if (!is_array($decoded)) {
					throw new InvalidArgumentException('YAML attributes must decode to an object or array.');
				}
				$this->attributes = is_string($attributes)
					? $decoded
					: [self::MODEL_FROM_YAML => $decoded];
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
		
		if(isset($schema[self::MODEL_FROM_YAML])) {
			$jsonSchema = json_encode($schema[self::MODEL_FROM_YAML], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		} elseif (isset($schema[self::MODEL_FROM_JSON])) {
			$jsonSchema = $schema[self::MODEL_FROM_JSON];
		} else {
			$jsonSchema = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		}
		

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
