<?php

declare(strict_types=1);

namespace Maclof\Kubernetes\Repositories;

use Maclof\Kubernetes\Collections\SubnamespaceAnchorCollection;
use Maclof\Kubernetes\Models\SubnamespaceAnchor;
use Maclof\Kubernetes\Collections\Collection;

/**
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class SubnamespaceAnchorRepository extends Repository
{
    protected string $uri = 'subnamespacesanchors';

    /**
     * @param array{items: array<int, array<mixed>|SubnamespaceAnchor>} $response
     */
    protected function createCollection(array $response): Collection
    {
        return new SubnamespaceAnchorCollection($response['items']);
    }
}
