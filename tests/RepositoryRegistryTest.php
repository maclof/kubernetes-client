<?php

use Maclof\Kubernetes\RepositoryRegistry;

class RepositoryRegistryTest extends TestCase
{

    public function test_builtin_repositories()
    {
        $registry = new RepositoryRegistry();

        $this->assertCount(22, $registry);
    }

    public function test_add_repository()
    {
        $registry = new RepositoryRegistry();
        $class = '\Example\Class';

        $this->assertFalse(isset($registry['test']));

        $registry['test'] = $class;

        $this->assertTrue(isset($registry['test']));
        $this->assertEquals($class, $registry['test']);
    }
}
