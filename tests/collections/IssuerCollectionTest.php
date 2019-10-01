<?php

use Maclof\Kubernetes\Collections\IssuerCollection;

class IssuerCollectionTest extends TestCase
{
    protected $items = [
        [],
        [],
        [],
    ];

    protected function getIssuerCollection()
    {
        $issuerCollection = new IssuerCollection($this->items);

        return $issuerCollection;
    }

    public function test_get_items()
    {
        $issuerCollection = $this->getIssuerCollection();
        $items = $issuerCollection->toArray();

        $this->assertTrue(is_array($items));
        $this->assertEquals(3, count($items));
    }
}
