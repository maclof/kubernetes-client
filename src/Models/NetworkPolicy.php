<?php
/**
 * Created by PhpStorm.
 * User: leon
 * Date: 13-04-18
 * Time: 14:29
 */

namespace Maclof\Kubernetes\Models;

class NetworkPolicy extends Model
{
    /**
     * The api version.
     *
     * @var string
     */
    protected $apiVersion = 'networking.k8s.io/v1';
}
