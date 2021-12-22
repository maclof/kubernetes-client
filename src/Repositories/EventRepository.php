<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\EventCollection;

class EventRepository extends Repository
{
	protected string $uri = 'events';

	protected function createCollection($response): EventCollection
	{
		return new EventCollection($response['items']);
	}
}
