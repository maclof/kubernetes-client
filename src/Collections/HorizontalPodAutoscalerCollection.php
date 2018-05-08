<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\HorizontalPodAutoscaler;

class HorizontalPodAutoscalerCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($this->getHorizontalPodAutoscalers(isset($data['items']) ? $data['items'] : []));
	}

	/**
	 * Get an array of autoscalers.
	 *
	 * @param  array  $items
	 * @return array
	 */
	protected function getHorizontalPodAutoscalers(array $items)
	{
		foreach ($items as &$item) {
			$item = new HorizontalPodAutoscaler($item);
		}

		return $items;
	}
}
