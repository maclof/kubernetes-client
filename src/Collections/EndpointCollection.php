<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Endpoint;

class EndpointCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getEndpoints($items));
	}

	/**
	 * Get an array of nodes.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getEndpoints(array $items): array
	{
		foreach ($items as &$item) {
			if ($item instanceof Endpoint) {
				continue;
			}
			
			$item = new Endpoint($item);
		}

		return $items;
	}
}
