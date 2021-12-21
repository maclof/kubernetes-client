<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\DaemonSet;

class DaemonSetCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getDaemonSets($items));
	}

	/**
	 * Get an array of daemon sets.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getDaemonSets(array $items): array
	{
		foreach ($items as &$item) {
			if ($item instanceof DaemonSet) {
				continue;
			}
			
			$item = new DaemonSet($item);
		}

		return $items;
	}
}
