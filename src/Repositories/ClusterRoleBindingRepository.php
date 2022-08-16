<?php

declare(strict_types=1);

namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\ClusterRoleBindingCollection;
use Maclof\Kubernetes\Models\ClusterRoleBinding;
use Maclof\Kubernetes\Collections\Collection;

/**
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ClusterRoleBindingRepository extends Repository
{
    protected string $uri = 'clusterroles';

    protected bool $namespace = false;

    /**
     * @param array{items: array<int, array<mixed>|ClusterRoleBinding>} $response
     */
    protected function createCollection(array $response): Collection
    {
        return new ClusterRoleBindingCollection($response['items']);
    }
}
