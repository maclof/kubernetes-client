<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\NetworkPolicy;

class NetworkPolicyCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getPolicies($items));
	}

	/**
	 * Get an array of network policies.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getPolicies(array $items): array
	{
		foreach ($items as &$item) {
			if ($item instanceof NetworkPolicy) {
				continue;
			}
			
			$item = new NetworkPolicy($item);
		}

		return $items;
	}
}
