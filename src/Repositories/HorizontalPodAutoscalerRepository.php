<?php namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\HorizontalPodAutoscalerCollection;

class HorizontalPodAutoscalerRepository extends Repository
{
	protected string $uri = 'horizontalpodautoscalers';

	protected function createCollection($response): HorizontalPodAutoscalerCollection
	{
		return new HorizontalPodAutoscalerCollection($response['items']);
	}
}
