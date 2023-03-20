<?php namespace Dasann\Kubernetes\Collections;

use Dasann\Kubernetes\Models\ReplicationController;

class ReplicationControllerCollection extends Collection
{
	/**
	 * The constructor.
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getReplicationControllers($items));
	}

	/**
	 * Get an array of replication controllers.
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
