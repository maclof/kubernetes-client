<?php namespace Dasann\Kubernetes\Repositories;

use Dasann\Kubernetes\Collections\CertificateCollection;
use Dasann\Kubernetes\Repositories\Strategy\PatchMergeTrait;

class CertificateRepository extends Repository
{
    use PatchMergeTrait;

    protected string $uri = 'certificates';

    protected function createCollection($response): CertificateCollection
    {
        return new CertificateCollection($response['items']);
    }

}
