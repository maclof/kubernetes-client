<?php namespace Dasann\Kubernetes\Collections;

use Dasann\Kubernetes\Models\PersistentVolume;
use Dasann\Kubernetes\Collections\Collection;

class PersistentVolumeCollection extends Collection
{
	/**
	 * The constructor.
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getPersistentVolumes($items));
	}

	/**
	 * Get an array of persistent volumes.
	 */
	protected function getPersistentVolumes(array $items): array
	{
		foreach ($items as &$item) {
			if ($item instanceof PersistentVolume) {
				continue;
			}
			
			$item = new PersistentVolume($item);
		}

		return $items;
	}
}
