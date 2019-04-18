<?php namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Models\GameServer;
use Maclof\Kubernetes\Collections\GameServerCollection;

class GameServerRepository extends Repository
{
	protected $uri = 'gameservers';

	protected function createCollection($response)
	{
		return new GameServerCollection($response['items']);
	}

	/**
	 * Get the logs for a game server.
	 *
	 * @param  \Maclof\Kubernetes\Models\GameServer $gameServer
	 * @param  array $options
	 * @return string
	 */
	public function logs(GameServer $gameServer, array $options = [])
	{
		$response = $this->client->sendRequest('GET', '/' . $this->uri . '/' . $gameServer->getMetadata('name') . '/log', $options);

		return $response;
	}
	
	/**
	 * Execute a command on a game server.
	 *
	 * @param  \Maclof\Kubernetes\Models\GameServer $gameServer
	 * @param  array $options
	 * @return string
	 */
	public function exec(GameServer $gameServer, array $options = [])
	{
		$response = $this->client->sendRequest('POST', '/' . $this->uri . '/' . $gameServer->getMetadata('name') . '/exec', $options);

		return $response;
	}
}
