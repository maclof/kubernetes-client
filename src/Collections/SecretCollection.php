<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Secret;

class SecretCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($this->getSecrets(isset($data['items']) ? $data['items'] : []));
	}

	/**
	 * Get an array of secrets.
	 *
	 * @param  array  $items
	 * @return array
	 */
	protected function getSecrets(array $items)
	{
		foreach ($items as &$item) {
			$item = new Secret($item);
		}

		return $items;
	}
}
