<?php namespace Maclof\Kubernetes\Models;

abstract class BaseModel
{
	/**
	 * Get the schema.
	 * 
	 * @return array
	 */
	abstract public function getSchema();
}