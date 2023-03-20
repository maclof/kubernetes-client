<?php namespace Dasann\Kubernetes\Collections;

use Dasann\Kubernetes\Models\Secret;

class SecretCollection extends Collection
{
	/**
	 * The constructor.
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getSecrets($items));
	}

	/**
	 * Get an array of secrets.
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
