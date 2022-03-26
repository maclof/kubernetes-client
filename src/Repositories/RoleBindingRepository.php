<?php

declare(strict_types=1);

namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\RoleBindingCollection;
use Maclof\Kubernetes\Models\RoleBinding;
use Maclof\Kubernetes\Collections\Collection;

/**
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RoleBindingRepository extends Repository
{
    protected string $uri = 'rolebindings';

    /**
     * @param array{items: array<int, array<mixed>|RoleBinding>} $response
     */
    protected function createCollection(array $response): Collection
    {
        return new RoleBindingCollection($response['items']);
    }
}
