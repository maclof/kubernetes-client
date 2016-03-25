<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\ConfigMap;

class ConfigMapCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($this->getConfigMaps(isset($data['items']) ? $data['items'] : []));
	}

	/**
	 * Get an array of config maps.
	 *
	 * @param  array  $items
	 * @return array
	 */
	protected function getConfigMaps(array $items)
	{
		foreach ($items as &$item) {
			$item = new ConfigMap($item);
		}

		return $items;
	}
}
