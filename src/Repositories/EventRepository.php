<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\EventCollection;

class EventRepository extends Repository
{
	protected $uri = 'events';

	protected function createCollection($response)
	{
		return new EventCollection($response);
	}
}
