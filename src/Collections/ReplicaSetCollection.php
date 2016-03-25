<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\ReplicaSet;

class ReplicaSetCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($this->getReplicaSets(isset($data['items']) ? $data['items'] : []));
	}

	/**
	 * Get an array of replication sets.
	 *
	 * @param  array  $items
	 * @return array
	 */
	protected function getReplicaSets(array $items)
	{
		foreach ($items as &$item) {
			$item = new ReplicaSet($item);
		}

		return $items;
	}
}
