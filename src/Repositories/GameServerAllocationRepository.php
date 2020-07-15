<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Models\GameServerAllocation;
use Maclof\Kubernetes\Collections\GameServerAllocationCollection;

class GameServerAllocationRepository extends Repository
{
	protected $uri = 'gameserverallocations';

	protected function createCollection($response)
	{
		return new GameServerAllocationCollection($response['items']);
	}

	/**
	 * Get the logs for a GameServerAllocation.
	 *
	 * @param  \Maclof\Kubernetes\Models\GameServerAllocation $gameServerAllocation
	 * @param  array $options
	 * @return string
	 */
	public function logs(GameServerAllocation $gameServerAllocation, array $options = [])
	{
		$response = $this->client->sendRequest('GET', '/' . $this->uri . '/' . $gameServerAllocation->getMetadata('name') . '/log', $options);

		return $response;
	}
	
	/**
	 * Execute a command on a GameServerAllocation.
	 *
	 * @param  \Maclof\Kubernetes\Models\GameServerAllocation $gameServerAllocation
	 * @param  array $options
	 * @return string
	 */
	public function exec(GameServerAllocation $gameServerAllocation, array $options = [])
	{
		$response = $this->client->sendRequest('POST', '/' . $this->uri . '/' . $gameServerAllocation->getMetadata('name') . '/exec', $options);

		return $response;
	}
}
