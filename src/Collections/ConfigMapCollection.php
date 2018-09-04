<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\ConfigMap;

class ConfigMapCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getConfigMaps($items));
	}

	/**
	 * Get an array of config maps.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getConfigMaps(array $items)
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
