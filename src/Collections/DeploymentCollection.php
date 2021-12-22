<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Deployment;

class DeploymentCollection extends Collection
{
	/**
	 * The constructor.
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->getDeployments($items));
	}

	/**
	 * Get an array of deployments.
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
