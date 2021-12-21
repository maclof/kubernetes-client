<?php namespace Maclof\Kubernetes\Models;

class NetworkPolicy extends Model
{
	/**
	 * The api version.
	 */
	protected string $apiVersion = 'networking.k8s.io/v1';
}
