<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Models\FleetAllocation;
use Maclof\Kubernetes\Collections\FleetAllocationCollection;

class FleetAllocationRepository extends Repository
{
	protected $uri = 'fleetallocations';

	protected function createCollection($response)
	{
		return new FleetAllocationCollection($response['items']);
	}

	/**
	 * Get the logs for a FleetAllocation.
	 *
	 * @param  \Maclof\Kubernetes\Models\FleetAllocation $fleetAllocation
	 * @param  array $options
	 * @return string
	 */
	public function logs(FleetAllocation $fleetAllocation, array $options = [])
	{
		$response = $this->client->sendRequest('GET', '/' . $this->uri . '/' . $fleetAllocation->getMetadata('name') . '/log', $options);

		return $response;
	}
	
	/**
	 * Execute a command on a FleetAllocation.
	 *
	 * @param  \Maclof\Kubernetes\Models\FleetAllocation $fleetAllocation
	 * @param  array $options
	 * @return string
	 */
	public function exec(FleetAllocation $fleetAllocation, array $options = [])
	{
		$response = $this->client->sendRequest('POST', '/' . $this->uri . '/' . $fleetAllocation->getMetadata('name') . '/exec', $options);

		return $response;
	}
}
