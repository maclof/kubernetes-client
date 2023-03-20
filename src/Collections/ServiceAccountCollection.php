<?php

declare(strict_types=1);

namespace Dasann\Kubernetes\Collections;

use Dasann\Kubernetes\Models\ServiceAccount;

/**
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ServiceAccountCollection extends Collection
{
    /**
     * @param array<int, array<mixed>|ServiceAccount> $items
     */
    public function __construct(array $items)
    {
        parent::__construct($this->getServiceAccounts($items));
    }

    /**
     * Get an array of serviceAccounts.
     *
     * @param  array<int, array<mixed>|ServiceAccount> $items
     * @return array<ServiceAccount>
     */
    protected function getServiceAccounts(array $items)
    {
        $final = [];
        foreach ($items as &$item) {
            if (!$item instanceof ServiceAccount) {
                $final[] = new ServiceAccount($item);
            } else {
                $final[] = $item;
            }
        }

        return $final;
    }
}
