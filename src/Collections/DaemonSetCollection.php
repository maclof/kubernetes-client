<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\DaemonSet;

class DaemonSetCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($this->getDaemonSets(isset($data['items']) ? $data['items'] : []));
	}

	/**
	 * Get an array of daemon sets.
	 *
	 * @param  array  $items
	 * @return array
	 */
	protected function getDaemonSets(array $items)
	{
		foreach ($items as &$item) {
			$item = new DaemonSet($item);
		}

		return $items;
	}
}
