<?php namespace Maclof\Kubernetes\Models;

class CronJob extends Model
{
	/**
	 * The api version.
	 */
	protected string $apiVersion = 'batch/v1beta1';
}
