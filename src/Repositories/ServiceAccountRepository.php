<?php

declare(strict_types=1);

namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\ServiceAccountCollection;
use Dasann\Kubernetes\Models\ServiceAccount;
use Dasann\Kubernetes\Collections\Collection;

/**
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ServiceAccountRepository extends Repository
{
    protected string $uri = 'serviceaccounts';

    /**
     * @param array{items: array<int, array<mixed>|ServiceAccount>} $response
     */
    protected function createCollection(array $response): Collection
    {
        return new ServiceAccountCollection($response['items']);
    }
}
