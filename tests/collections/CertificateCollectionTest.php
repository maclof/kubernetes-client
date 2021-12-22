<?php

use Maclof\Kubernetes\Collections\CertificateCollection;

class CertificateCollectionTest extends TestCase
{
    protected array $items = [
        [],
        [],
        [],
    ];

    protected function getCertificateCollection(): CertificateCollection
    {
        $configMapCollection = new CertificateCollection($this->items);

        return $configMapCollection;
    }

    public function test_get_items(): void
    {
        $certificateCollection = $this->getCertificateCollection();
        $items = $certificateCollection->toArray();

        $this->assertTrue(is_array($items));
        $this->assertEquals(3, count($items));
    }
}
