<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\ReplicationController;

class ReplicationControllerCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getReplicationControllers($items));
	}

	/**
	 * Get an array of replication controllers.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getReplicationControllers(array $items): array
	{
		foreach ($items as &$item) {
			if ($item instanceof ReplicationController) {
				continue;
			}
			
			$item = new ReplicationController($item);
		}

		return $items;
	}
}
