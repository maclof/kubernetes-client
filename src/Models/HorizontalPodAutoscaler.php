<?php namespace Maclof\Kubernetes\Models;

class HorizontalPodAutoscaler extends \Maclof\Kubernetes\Models\Model
{
	/**
	 * The api version.
	 *
	 * @var string
	 */
	protected $apiVersion = 'autoscaling/v2beta1';
}
