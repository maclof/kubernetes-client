<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\ReplicaSet;

class ReplicaSetCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getReplicaSets($items));
	}

	/**
	 * Get an array of replication sets.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getReplicaSets(array $items)
	{
		foreach ($items as &$item) {
			if ($item instanceof ReplicaSet) {
				continue;
			}
			
			$item = new ReplicaSet($item);
		}

		return $items;
	}
}
