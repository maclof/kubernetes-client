<?php namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\IssuerCollection;
use Dasann\Kubernetes\Repositories\Strategy\PatchMergeTrait;

class IssuerRepository extends Repository
{
    use PatchMergeTrait;

    protected string $uri = 'issuers';

    protected function createCollection($response): IssuerCollection
    {
        return new IssuerCollection($response['items']);
    }

}
