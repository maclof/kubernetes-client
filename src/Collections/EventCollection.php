<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Event;

class EventCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getEvents($items));
	}

	/**
	 * Get an array of nodes.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getEvents(array $items)
	{
		foreach ($items as &$item) {
			if ($item instanceof Event) {
				continue;
			}
			
			$item = new Event($item);
		}

		return $items;
	}
}
