<?php namespace Maclof\Kubernetes\Models;

class NetworkPolicy extends Model
{
	/**
	 * The api version.
	 *
	 * @var string
	 */
	protected string $apiVersion = 'networking.k8s.io/v1';
}
