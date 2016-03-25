<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Node;

class NodeCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($this->getNodes(isset($data['items']) ? $data['items'] : []));
	}

	/**
	 * Get an array of nodes.
	 *
	 * @param  array  $items
	 * @return array
	 */
	protected function getNodes(array $items)
	{
		foreach ($items as &$item) {
			$item = new Node($item);
		}

		return $items;
	}
}
