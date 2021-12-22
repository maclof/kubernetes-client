<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Ingress;

class IngressCollection extends Collection
{
	/**
	 * The constructor.
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getIngresses($items));
	}

	/**
	 * Get an array of Ingresses.
	 */
	protected function getIngresses(array $items): array
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
