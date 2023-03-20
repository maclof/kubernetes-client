<?php

declare(strict_types=1);

namespace Dasann\Kubernetes\Models;

/**
  * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Role extends Model
{
    /**
     * @var string
     */
    protected string $apiVersion = 'rbac.authorization.k8s.io/v1';
}
