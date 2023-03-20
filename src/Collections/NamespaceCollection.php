<?php namespace Dasann\Kubernetes\Collections;

use Dasann\Kubernetes\Models\NamespaceModel;

class NamespaceCollection extends Collection
{
	/**
	 * The constructor.
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getNamespaces($items));
	}

	/**
	 * Get an array of Namespaces.
	 */
	protected function getNamespaces(array $items): array
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
