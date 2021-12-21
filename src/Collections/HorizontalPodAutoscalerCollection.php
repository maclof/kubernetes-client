<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\HorizontalPodAutoscaler;

class HorizontalPodAutoscalerCollection extends Collection
{
	/**
	 * The constructor.
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getHorizontalPodAutoscalers($items));
	}

	/**
	 * Get an array of autoscalers.
	 */
	protected function getHorizontalPodAutoscalers(array $items): array
	{
		foreach ($items as &$item) {
			if ($item instanceof HorizontalPodAutoscaler) {
				continue;
			}
			
			$item = new HorizontalPodAutoscaler($item);
		}

		return $items;
	}
}
