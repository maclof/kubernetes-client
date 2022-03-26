<?php

declare(strict_types=1);

namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\RoleCollection;
use Maclof\Kubernetes\Models\Role;
use Maclof\Kubernetes\Collections\Collection;

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
