<?php

use Fist\Testing\TestCase;
use Fist\Container\Container;
use Fist\Repository\ArrayRepository;
use Fist\Repository\ContainerRepository;
use Fist\Repository\RepositoryInterface;

class RepositoryTest extends TestCase
{
    public function testRepositoryBuilding()
    {
        $this->assertInstanceOf(
            RepositoryInterface::class,
            new ArrayRepository()
        );

        $this->assertInstanceOf(
            RepositoryInterface::class,
            new ContainerRepository(
                new Container()
            )
        );
    }

    public function testArrayRepositoryGetter()
    {
        $array = [
            'foo' => 'bar',
            'bar' => 'baz',
            'foobar' => [
                'foo' => 'bar',
            ],
        ];

        $repository = new ArrayRepository($array);

        $this->assertEquals('bar', $repository->get('foo'));
        $this->assertEquals('baz', $repository->get('bar'));
        $this->assertEquals(null, $repository->get('baz'));
        $this->assertEquals('baz', $repository->get('baz', 'baz'));
        $this->assertEquals('bar', $repository->get('foobar.foo'));
    }

    public function testArrayRepositorySetter()
    {
        $array = [
            'foo' => 'bar',
            'bar' => 'baz',
            'foobar' => [
                'foo' => 'bar',
            ],
        ];

        $repository = new ArrayRepository($array);

        $this->assertEquals('bar', $repository->get('foo'));

        $repository->set('foo', 'foobar');

        $this->assertEquals('foobar', $repository->get('foo'));

        $this->assertEquals(null, $repository->get('baz'));

        $repository->set('baz', 'baz');

        $this->assertEquals('baz', $repository->get('baz'));

        $this->assertEquals('bar', $repository->get('foobar.foo'));

        $repository->set('foobar.foo', 'baz');

        $this->assertEquals('baz', $repository->get('foobar.foo'));

        $this->assertEquals(null, $repository->get('foobar.bar'));

        $repository->set('foobar.bar', 'baz');

        $this->assertEquals('baz', $repository->get('foobar.bar'));
    }

    public function testContainerRepositoryGetter()
    {
        $repository = new ContainerRepository(
            $container = new Container()
        );

        $container->instance('foo', 'bar');
        $container->instance('bar', 'baz');
        $container->instance('foobar.foo', 'bar');

        $this->assertEquals('bar', $repository->get('foo'));
        $this->assertEquals('baz', $repository->get('bar'));
        $this->assertEquals(null, $repository->get('baz'));
        $this->assertEquals('baz', $repository->get('baz', 'baz'));
        $this->assertEquals('bar', $repository->get('foobar.foo'));
    }

    public function testContainerRepositorySetter()
    {
        $repository = new ContainerRepository(
            $container = new Container()
        );

        $container->instance('foo', 'bar');
        $container->instance('bar', 'baz');
        $container->instance('foobar.foo', 'bar');

        $this->assertEquals('bar', $repository->get('foo'));

        $repository->set('foo', 'foobar');

        $this->assertEquals('foobar', $repository->get('foo'));

        $this->assertEquals(null, $repository->get('baz'));

        $repository->set('baz', 'baz');

        $this->assertEquals('baz', $repository->get('baz'));

        $this->assertEquals('bar', $repository->get('foobar.foo'));

        $repository->set('foobar.foo', 'baz');

        $this->assertEquals('baz', $repository->get('foobar.foo'));

        $this->assertEquals(null, $repository->get('foobar.bar'));

        $repository->set('foobar.bar', 'baz');

        $this->assertEquals('baz', $repository->get('foobar.bar'));
    }
}
