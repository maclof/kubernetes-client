<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Secret;

class SecretCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getSecrets($items));
	}

	/**
	 * Get an array of secrets.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getSecrets(array $items): array
	{
		foreach ($items as &$item) {
			if ($item instanceof Secret) {
				continue;
			}
			
			$item = new Secret($item);
		}

		return $items;
	}
}
