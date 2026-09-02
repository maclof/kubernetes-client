<?php namespace Maclof\Kubernetes\Collections;

use Illuminate\Support\Collection as IlluminateCollection;

abstract class Collection extends IlluminateCollection
{
	/**
	 * Mapping may change the item type, so return a normal collection rather
	 * than trying to reconstruct a resource-specific collection.
	 */
	public function map(callable $callback)
	{
		return (new IlluminateCollection($this->items))->map($callback);
	}
}
