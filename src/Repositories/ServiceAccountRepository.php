<?php

declare(strict_types=1);

namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\ServiceAccountCollection;
use Maclof\Kubernetes\Models\ServiceAccount;
use Maclof\Kubernetes\Collections\Collection;

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
