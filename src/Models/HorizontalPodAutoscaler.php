<?php namespace Dasann\Kubernetes\Models;

class HorizontalPodAutoscaler extends \Dasann\Kubernetes\Models\Model
{
	/**
	 * The api version.
	 */
	protected string $apiVersion = 'autoscaling/v2';
}
