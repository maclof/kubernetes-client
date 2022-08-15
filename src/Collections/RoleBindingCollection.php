<?php

declare(strict_types=1);

namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\RoleBinding;

/**
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RoleBindingCollection extends Collection
{
    /**
     * @param array<int, array<mixed>|RoleBinding> $items
     */
    public function __construct(array $items)
    {
        parent::__construct($this->getItems($items));
    }

    /**
     * Get an array of serviceAccounts.
     *
     * @param  array<int, array<mixed>|RoleBinding> $items
     * @return array<RoleBinding>
     */
    protected function getItems(array $items)
    {
        $final = [];
        foreach ($items as &$item) {
            if (!$item instanceof RoleBinding) {
                $final[] = new RoleBinding($item);
            } else {
                $final[] = $item;
            }
        }

        return $final;
    }
}
