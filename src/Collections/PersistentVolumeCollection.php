<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\PersistentVolume;
use Maclof\Kubernetes\Collections\Collection;

class PersistentVolumeCollection extends Collection
{
	public function __construct(array $data)
	{
		parent::__construct($this->getPersistentVolume(isset($data['items']) ? $data['items'] : []));
	}

	protected function getPersistentVolume(array $items)
	{
		foreach ($items as &$item) {
			$item = new PersistentVolume($item);
		}

		return $items;
	}
}

