<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\QuotaModel;

class QuotaCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($this->getQuotas(isset($data['items']) ? $data['items'] : []));
	}

	/**
	 * Get an array of Namespaces.
	 *
	 * @param  array  $items
	 * @return array
	 */
	protected function getQuotas(array $items)
	{
		foreach ($items as &$item) {
			$item = new QuotaModel($item);
		}

		return $items;
	}
}
