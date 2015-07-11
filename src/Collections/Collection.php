<?php namespace Maclof\Kubernetes\Collections;

abstract class Collection
{
	/**
	 * The items.
	 * 
	 * @var array
	 */
	protected $items = [];

	/**
	 * The constructor.
	 * 
	 * @param array $data
	 */
	public function __construct(array $items)
	{
		$this->items = $items;
	}

	/**
	 * Get the items.
	 * 
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}
}