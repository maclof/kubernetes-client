<?php namespace Dasann\Kubernetes\Collections;

use Dasann\Kubernetes\Models\ConfigMap;

class ConfigMapCollection extends Collection
{
	/**
	 * The constructor.
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getConfigMaps($items));
	}

	/**
	 * Get an array of config maps.
	 */
	protected function getConfigMaps(array $items): array
	{
		foreach ($items as &$item) {
			if ($item instanceof ConfigMap) {
				continue;
			}
			
			$item = new ConfigMap($item);
		}

		return $items;
	}
}
