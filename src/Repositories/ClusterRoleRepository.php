<?php

declare(strict_types=1);

namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\ClusterRoleCollection;
use Maclof\Kubernetes\Models\ClusterRole;
use Maclof\Kubernetes\Collections\Collection;

/**
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ClusterRoleRepository extends Repository
{
    protected string $uri = 'roles';

    /**
     * @param array{items: array<int, array<mixed>|ClusterRole>} $response
     */
    protected function createCollection(array $response): Collection
    {
        return new ClusterRoleCollection($response['items']);
    }
}
