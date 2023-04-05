<?php

declare(strict_types=1);

namespace Maclof\Kubernetes\Models;

/**
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class SubnamespaceAnchor extends Model
{
    /**
     * @var string
     */
    protected string $apiVersion = 'hnc.x-k8s.io/v1';
}
