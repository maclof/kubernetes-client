<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\DynamicResource;

class DynamicResourceCollection extends Collection
{
	public function __construct(array $items)
	{
		parent::__construct(array_map(function ($item) {
			return $item instanceof DynamicResource ? $item : new DynamicResource($item);
		}, $items));
	}
}
