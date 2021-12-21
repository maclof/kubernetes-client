<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\PersistentVolumeClaim;

class PersistentVolumeClaimCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getPersistentVolumeClaims($items));
	}

	/**
	 * Get an array of persistent volume claims.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getPersistentVolumeClaims(array $items): array
	{
		foreach ($items as &$item) {
			if ($item instanceof PersistentVolumeClaim) {
				continue;
			}
			
			$item = new PersistentVolumeClaim($item);
		}

		return $items;
	}
}
