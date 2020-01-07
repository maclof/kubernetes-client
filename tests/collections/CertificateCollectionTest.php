<?php

use Maclof\Kubernetes\Collections\CertificateCollection;

class CertificateCollectionTest extends TestCase
{
    protected $items = [
        [],
        [],
        [],
    ];

    protected function getCertificateCollection()
    {
        $configMapCollection = new CertificateCollection($this->items);

        return $configMapCollection;
    }

    public function test_get_items()
    {
        $certificateCollection = $this->getCertificateCollection();
        $items = $certificateCollection->toArray();

        $this->assertTrue(is_array($items));
        $this->assertEquals(3, count($items));
    }
}
