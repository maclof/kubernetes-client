<?php

use Maclof\Kubernetes\Models\Issuer;

class IssuerTest extends TestCase
{
    public function test_get_schema(): void
    {
        $issuer = new Issuer;

        $schema = trim($issuer->getSchema());
        $fixture = trim($this->getFixture('issuers/empty.json'));

        $this->assertEquals($fixture, $schema);
    }

    public function test_get_metadata(): void
    {
        $issuer = new Issuer([
            'metadata' => [
                'name' => 'test',
            ],
        ]);

        $metadata = $issuer->getMetadata('name');

        $this->assertEquals($metadata, 'test');
    }
}
