<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Ingress;

class IngressCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getIngresses($items));
	}

	/**
	 * Get an array of Ingresses.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getIngresses(array $items)
	{
		foreach ($items as &$item) {
			if ($item instanceof Ingress) {
				continue;
			}
			
			$item = new Ingress($item);
		}

		return $items;
	}
}
