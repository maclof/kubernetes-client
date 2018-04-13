<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\NetworkPolicy;

class NetworkPolicyCollection extends Collection
{
    /**
     * The constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($this->getPolicies(isset($data['items']) ? $data['items'] : []));
    }

    /**
     * Get an array of network policies.
     *
     * @param  array  $items
     * @return array
     */
    protected function getPolicies(array $items)
    {
        foreach ($items as &$item) {
            $item = new NetworkPolicy($item);
        }

        return $items;
    }
}