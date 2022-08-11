<?php

declare(strict_types=1);

namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\ClusterRole;

/**
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ClusterRoleCollection extends Collection
{
    /**
     * @param array<int, array<mixed>|ClusterRole> $items
     */
    public function __construct(array $items)
    {
        parent::__construct($this->getServiceAccounts($items));
    }

    /**
     * Get an array of serviceAccounts.
     *
     * @param  array<int, array<mixed>|ClusterRole> $items
     * @return array<ClusterRole>
     */
    protected function getServiceAccounts(array $items)
    {
        $final = [];
        foreach ($items as &$item) {
            if (!$item instanceof ClusterRole) {
                $final[] = new ClusterRole($item);
            } else {
                $final[] = $item;
            }
        }

        return $final;
    }
}
