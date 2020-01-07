<?php

use Maclof\Kubernetes\Models\Certificate;

class CertificateTest extends TestCase
{
    public function test_get_schema()
    {
        $certificate = new Certificate;

        $schema = trim($certificate->getSchema());
        $fixture = trim($this->getFixture('certificates/empty.json'));

        $this->assertEquals($fixture, $schema);
    }

    public function test_get_metadata()
    {
        $certificate = new Certificate([
            'metadata' => [
                'name' => 'test',
            ],
        ]);

        $metadata = $certificate->getMetadata('name');

        $this->assertEquals($metadata, 'test');
    }
}
