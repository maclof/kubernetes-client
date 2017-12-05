<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\NamespaceModel;

class NamespaceCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($this->getNamespaces(isset($data['items']) ? $data['items'] : []));
	}

	/**
	 * Get an array of Namespaces.
	 *
	 * @param  array  $items
	 * @return array
	 */
	protected function getNamespaces(array $items)
	{
		foreach ($items as &$item) {
			$item = new NamespaceModel($item);
		}

		return $items;
	}
}
