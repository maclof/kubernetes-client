<?php

namespace Maclof\Kubernetes\Models;

use Flow\JSONPath\JSONPath;
use TestCase;

class ModelTest extends TestCase
{
    public function testGetJsonPath()
    {
        $expected = 'foo';

        $model = new ConcreteModel(['metadata' => ['name' => 'foo']]);

        $actual = $model->getJsonPath('$.metadata.name');

        $this->assertInstanceOf(JSONPath::class, $actual);
        $this->assertCount(1, $actual);
        $this->assertEquals($expected, $actual[0]);
    }

    public function testIsGettingSchema()
    {
        $expected = json_encode([
            'kind' => 'ConcreteModel',
            'apiVersion' => 'v1',
        ], JSON_PRETTY_PRINT);

        $model = new ConcreteModel([]);

        $actual = $model->getSchema();

        $this->assertJson($actual);
        $this->assertEquals($expected, $actual);
    }

    public function testGetApiVersion()
    {
        $expected = 'v1';

        $model = new ConcreteModel();

        $actual = $model->getApiVersion();

        $this->assertEquals($expected, $actual);
    }

    public function testGetMetadata()
    {
        $expected = 'foo';

        $model = new ConcreteModel(['metadata' => ['name' => 'foo']]);

        $actual = $model->getMetadata('name');

        $this->assertEquals($expected, $actual);
    }

    public function testIsConvertingToArray()
    {
        $expected = ['foo' => 'bar'];

        $model = new ConcreteModel(['foo' => 'bar']);

        $actual = $model->toArray();

        $this->assertEquals($expected, $actual);

    }

    public function testIsReturningSchemaOnToStringCall()
    {
        $model = new ConcreteModel();

        $actual = (string) $model;

        $this->assertEquals($model->getSchema(), $actual);
    }
}

class ConcreteModel extends Model {}