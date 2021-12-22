<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Event;

class EventCollection extends Collection
{
	/**
	 * The constructor.
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getEvents($items));
	}

	/**
	 * Get an array of nodes.
	 */
	protected function getEvents(array $items): array
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
