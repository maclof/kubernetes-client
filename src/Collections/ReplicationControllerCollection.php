<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\ReplicationController;

class ReplicationControllerCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($this->getReplicationControllers(isset($data['items']) ? $data['items'] : []));
	}

	/**
	 * Get an array of replication controllers.
	 *
	 * @param  array  $items
	 * @return array
	 */
	protected function getReplicationControllers(array $items)
	{
		foreach ($items as &$item) {
			$item = new ReplicationController($item);
		}

		return $items;
	}
}
