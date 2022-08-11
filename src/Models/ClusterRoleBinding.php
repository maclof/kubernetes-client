<?php

declare(strict_types=1);

namespace Maclof\Kubernetes\Models;

/**
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ClusterRoleBinding extends Model
{
    /**
     * @var string
     */
    protected string $apiVersion = 'rbac.authorization.k8s.io/v1';
}
