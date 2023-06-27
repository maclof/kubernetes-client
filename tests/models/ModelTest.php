<?php

namespace Maclof\Kubernetes\Models;

use Flow\JSONPath\JSONPath;
use TestCase;

class ModelTest extends TestCase
{
    public function testGetJsonPath(): void
    {
        $expected = 'foo';

        $model = new ConcreteModel(['metadata' => ['name' => 'foo']]);

        $actual = $model->getJsonPath('$.metadata.name');

        $this->assertInstanceOf(JSONPath::class, $actual);
        $this->assertCount(1, $actual);
        $this->assertEquals($expected, $actual[0]);
    }

    public function testIsGettingSchema(): void
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

    public function testGetApiVersion(): void
    {
        $expected = 'v1';

        $model = new ConcreteModel();

        $actual = $model->getApiVersion();

        $this->assertEquals($expected, $actual);
    }

    public function testGetMetadata(): void
    {
		$model = new ConcreteModel(['metadata' => ['name' => 'foo', 'labels' => ['foo' => 'bar']]]);

		$expected = 'foo';

		$actual = $model->getMetadata('name');

        $this->assertEquals($expected, $actual);

		$expected = ['foo' => 'bar'];

		$actual = $model->getMetadata('labels');

		$this->assertEquals($expected, $actual);
    }

    public function testIsConvertingToArray(): void
    {
        $expected = ['foo' => 'bar'];

        $model = new ConcreteModel(['foo' => 'bar']);

        $actual = $model->toArray();

        $this->assertEquals($expected, $actual);

    }

    public function testIsReturningSchemaOnToStringCall(): void
    {
        $model = new ConcreteModel();

        $actual = (string) $model;

        $this->assertEquals($model->getSchema(), $actual);
    }
}

class ConcreteModel extends Model {}
