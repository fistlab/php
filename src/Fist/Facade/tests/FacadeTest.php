<?php

use Fist\Container\Container;
use Fist\Facade\ContainerFacade;
use Fist\Facade\Facade;
use Fist\Testing\TestCase;

class FacadeTest extends TestCase
{
    public function testCallingFacade()
    {
        $this->assertEquals('foo', ExampleFacade::foo());
    }

    public function testFacadeParsesArguments()
    {
        $this->assertEquals('bar', ExampleFacade::text('bar'));
    }

    /**
     * @expectedException Fist\Facade\InvalidArgumentException
     * @expectedExceptionMessage Method [notExistingMethod] does not exists.
     */
    public function testFacadeThrowsException()
    {
        ExampleFacade::notExistingMethod();
    }

    public function testContainerFacadeBuilds()
    {
        $container = new Container();

        Container::setInstance($container);

        $container->instance('example', new ExampleInstance());

        $this->assertEquals('foo', ExampleContainerFacade::foo());
    }

    /**
     * @expectedException ReflectionException
     * @expectedExceptionMessage Class example does not exist
     */
    public function testContainerFacadeThrowException()
    {
        $container = new Container();

        Container::setInstance($container);

        $this->assertEquals('foo', ExampleContainerFacade::foo());
    }
}

class ExampleInstance
{
    public function foo()
    {
        return 'foo';
    }

    public function text($text)
    {
        return $text;
    }
}

class ExampleFacade extends Facade
{
    public static function getFacadeInstance()
    {
        return new ExampleInstance();
    }
}

class ExampleContainerFacade extends ContainerFacade
{
    public static function getFacadeAccessor()
    {
        return 'example';
    }
}
