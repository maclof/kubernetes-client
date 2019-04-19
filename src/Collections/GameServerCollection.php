<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\GameServer;

class GameServerCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getGameServers($items));
	}

	/**
	 * Get an array of pods.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getGameServers(array $items)
	{
		foreach ($items as &$item) {
			if ($item instanceof GameServer) {
				continue;
			}
			
			$item = new GameServer($item);
		}

		return $items;
	}
}
