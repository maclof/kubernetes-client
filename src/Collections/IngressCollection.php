<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Ingress;

class IngressCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($this->getIngresses(isset($data['items']) ? $data['items'] : []));
	}

	/**
	 * Get an array of Ingresses.
	 *
	 * @param  array  $items
	 * @return array
	 */
	protected function getIngresses(array $items)
	{
		foreach ($items as &$item) {
			$item = new Ingress($item);
		}

		return $items;
	}
}
