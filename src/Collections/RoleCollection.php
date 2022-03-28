<?php

declare(strict_types=1);

namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Role;

/**
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RoleCollection extends Collection
{
    /**
     * @param array<int, array<mixed>|Role> $items
     */
    public function __construct(array $items)
    {
        parent::__construct($this->getServiceAccounts($items));
    }

    /**
     * Get an array of serviceAccounts.
     *
     * @param  array<int, array<mixed>|Role> $items
     * @return array<Role>
     */
    protected function getServiceAccounts(array $items)
    {
        $final = [];
        foreach ($items as &$item) {
            if (!$item instanceof Role) {
                $final[] = new Role($item);
            } else {
                $final[] = $item;
            }
        }

        return $final;
    }
}
