<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Service;

class ServiceCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($this->getServices(isset($data['items']) ? $data['items'] : []));
	}

	/**
	 * Get an array of services.
	 *
	 * @param  array  $items
	 * @return array
	 */
	protected function getServices(array $items)
	{
		foreach ($items as &$item) {
			$item = new Service($item);
		}

		return $items;
	}
}
