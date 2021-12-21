<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Deployment;

class DeploymentCollection extends Collection
{
	/**
	 * The constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getDeployments($items));
	}

	/**
	 * Get an array of deployments.
	 *
	 * @param  array $items
	 * @return array
	 */
	protected function getDeployments(array $items): array
	{
		foreach ($items as &$item) {
			if ($item instanceof Deployment) {
				continue;
			}
			
			$item = new Deployment($item);
		}

		return $items;
	}
}
