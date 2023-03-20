<?php namespace Dasann\Kubernetes\Collections;

use Dasann\Kubernetes\Models\Endpoint;

class EndpointCollection extends Collection
{
	/**
	 * The constructor.
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getEndpoints($items));
	}

	/**
	 * Get an array of nodes.
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
