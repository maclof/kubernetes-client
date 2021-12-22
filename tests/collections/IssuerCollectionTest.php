<?php

use Maclof\Kubernetes\Collections\IssuerCollection;

class IssuerCollectionTest extends TestCase
{
    protected array $items = [
        [],
        [],
        [],
    ];

    protected function getIssuerCollection(): IssuerCollection
    {
        $issuerCollection = new IssuerCollection($this->items);

        return $issuerCollection;
    }

    public function test_get_items(): void
    {
        $issuerCollection = $this->getIssuerCollection();
        $items = $issuerCollection->toArray();

        $this->assertTrue(is_array($items));
        $this->assertEquals(3, count($items));
    }
}
