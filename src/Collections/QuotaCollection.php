<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\QuotaModel;

class QuotaCollection extends Collection
{
	/**
	 * The constructor.
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getQuotas($items));
	}

	/**
	 * Get an array of Namespaces.
	 */
	protected function getQuotas(array $items): array
	{
		foreach ($items as &$item) {
			if ($item instanceof QuotaModel) {
				continue;
			}
			
			$item = new QuotaModel($item);
		}

		return $items;
	}
}
