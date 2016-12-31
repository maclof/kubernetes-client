<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Endpoint;

class EndpointCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($this->getEndpoints(isset($data['items']) ? $data['items'] : []));
	}

	/**
	 * Get an array of nodes.
	 *
	 * @param  array  $items
	 * @return array
	 */
	protected function getEndpoints(array $items)
	{
		foreach ($items as &$item) {
			$item = new Endpoint($item);
		}

		return $items;
	}
}
