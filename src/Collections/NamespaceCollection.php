<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\NamespaceModel;

class NamespaceCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getNamespaces($items));
	}

	/**
	 * Get an array of Namespaces.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getNamespaces(array $items)
	{
		foreach ($items as &$item) {
			if ($item instanceof NamespaceModel) {
				continue;
			}
			
			$item = new NamespaceModel($item);
		}

		return $items;
	}
}
