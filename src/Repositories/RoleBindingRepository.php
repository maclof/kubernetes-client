<?php

declare(strict_types=1);

namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\RoleBindingCollection;
use Dasann\Kubernetes\Models\RoleBinding;
use Dasann\Kubernetes\Collections\Collection;

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
