<?php

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
