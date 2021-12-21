<?php namespace Maclof\Kubernetes\Models;

class DaemonSet extends Model
{
	/**
	 * The api version.
	 *
	 * @var string
	 */
	protected string $apiVersion = 'extensions/v1beta1';
}
