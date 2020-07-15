<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\GameServerAllocation;

class GameServerAllocationCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getGameServerAllocations($items));
	}

	/**
	 * Get an array of pods.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getGameServerAllocations(array $items)
	{
		foreach ($items as &$item) {
			if ($item instanceof GameServerAllocation) {
				continue;
			}
			
			$item = new GameServerAllocation($item);
		}

		return $items;
	}
}
