<?php

use Maclof\Kubernetes\RepositoryRegistry;

class RepositoryRegistryTest extends TestCase
{

    public function test_builtin_repositories(): void
    {
        $registry = new RepositoryRegistry();

        $this->assertCount(28, $registry);
    }

    public function test_add_repository(): void
    {
        $registry = new RepositoryRegistry();
        $class = '\Example\Class';

        $this->assertFalse(isset($registry['test']));

        $registry['test'] = $class;

        $this->assertTrue(isset($registry['test']));
        $this->assertEquals($class, $registry['test']);
    }
}
