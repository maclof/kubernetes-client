<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\FleetAllocation;

class FleetAllocationCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getPods($items));
	}

	/**
	 * Get an array of pods.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getFleetAllocations(array $items)
	{
		foreach ($items as &$item) {
			if ($item instanceof FleetAllocation) {
				continue;
			}
			
			$item = new FleetAllocation($item);
		}

		return $items;
	}
}
