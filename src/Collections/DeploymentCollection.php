<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Deployment;

class DeploymentCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($this->getDeployments(isset($data['items']) ? $data['items'] : []));
	}

	/**
	 * Get an array of deployments.
	 *
	 * @param  array  $items
	 * @return array
	 */
	protected function getDeployments(array $items)
	{
		foreach ($items as &$item) {
			$item = new Deployment($item);
		}

		return $items;
	}
}
