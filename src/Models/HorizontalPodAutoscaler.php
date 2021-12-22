<?php namespace Maclof\Kubernetes\Models;

class HorizontalPodAutoscaler extends \Maclof\Kubernetes\Models\Model
{
	/**
	 * The api version.
	 */
	protected string $apiVersion = 'autoscaling/v2beta1';
}
