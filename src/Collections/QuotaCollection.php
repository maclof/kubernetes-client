<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\QuotaModel;

class QuotaCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getQuotas($items));
	}

	/**
	 * Get an array of Namespaces.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getQuotas(array $items)
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
