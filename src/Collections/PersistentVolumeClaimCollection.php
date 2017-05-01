<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\PersistentVolumeClaim;

class PersistentVolumeClaimCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($this->getPersistentVolumeClaims(isset($data['items']) ? $data['items'] : []));
	}

	/**
	 * Get an array of persistent volume claims.
	 *
	 * @param  array  $items
	 * @return array
	 */
	protected function getPersistentVolumeClaims(array $items)
	{
		foreach ($items as &$item) {
			$item = new PersistentVolumeClaim($item);
		}

		return $items;
	}
}
