<?php

declare(strict_types=1);

namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\ClusterRoleBinding;

/**
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ClusterRoleBindingCollection extends Collection
{
    /**
     * @param array<int, array<mixed>|ClusterRoleBinding> $items
     */
    public function __construct(array $items)
    {
        parent::__construct($this->getItems($items));
    }

    /**
     * Get an array of serviceAccounts.
     *
     * @param  array<int, array<mixed>|ClusterRoleBinding> $items
     * @return array<ClusterRoleBinding>
     */
    protected function getItems(array $items)
    {
        $final = [];
        foreach ($items as &$item) {
            if (!$item instanceof ClusterRoleBinding) {
                $final[] = new ClusterRoleBinding($item);
            } else {
                $final[] = $item;
            }
        }

        return $final;
    }
}
