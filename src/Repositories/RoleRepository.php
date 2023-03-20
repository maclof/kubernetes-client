<?php

declare(strict_types=1);

namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\RoleCollection;
use Dasann\Kubernetes\Models\Role;
use Dasann\Kubernetes\Collections\Collection;

/**
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RoleRepository extends Repository
{
    protected string $uri = 'roles';

    /**
     * @param array{items: array<int, array<mixed>|Role>} $response
     */
    protected function createCollection(array $response): Collection
    {
        return new RoleCollection($response['items']);
    }
}
