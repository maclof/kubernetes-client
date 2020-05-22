<?php namespace Maclof\Kubernetes\Models;

class Ingress extends Model
{
	/**
	 * The api version.
	 *
	 * @var string
	 */
	protected $apiVersion = 'networking.k8s.io/v1beta1';
}
