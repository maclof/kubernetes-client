<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Node;

class NodeCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getNodes($items));
	}

	/**
	 * Get an array of nodes.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getNodes(array $items): array
	{
		foreach ($items as &$item) {
			if ($item instanceof Node) {
				continue;
			}
			
			$item = new Node($item);
		}

		return $items;
	}
}
