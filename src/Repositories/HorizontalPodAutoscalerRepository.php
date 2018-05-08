<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\HorizontalPodAutoscalerCollection;

class HorizontalPodAutoscalerRepository extends Repository
{
	protected $uri = 'horizontalpodautoscalers';

	protected function createCollection($response)
	{
		return new HorizontalPodAutoscalerCollection($response);
	}
}
