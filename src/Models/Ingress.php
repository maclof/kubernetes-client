<?php namespace Dasann\Kubernetes\Models;

class Ingress extends Model
{
	/**
	 * The api version.
	 */
	protected string $apiVersion = 'networking.k8s.io/v1';
}
