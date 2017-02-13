<?php

use Fist\Testing\TestCase;
use Fist\Container\Container;

class ContainerTest extends TestCase
{
    public function testContainerBuilding()
    {
        $container = new Container();

        $this->assertInstanceOf(Container::class, $container);
    }

    public function testInstancing()
    {
        $application = new Container();

        Container::setInstance($application);

        $this->assertInstanceOf(Container::class, Container::getInstance());
        $this->assertSame($application, Container::getInstance());
    }

    public function testAutoInstancing()
    {
        Container::removeInstance();

        $this->assertSame(null, Container::getInstance());

        $application = new Container();

        $this->assertInstanceOf(Container::class, Container::getInstance());
        $this->assertSame($application, Container::getInstance());
    }

    public function testBinding()
    {
        $container = new Container();

        $container->bind('foo', function () {
            return 'bar';
        });

        $this->assertEquals('bar', $container->make('foo'));

        $container->bind(stdClass::class, function () {
            return new stdClass();
        });

        $this->assertNotSame(
            $container->make(stdClass::class),
            $container->make(stdClass::class)
        );
    }

    public function testBound()
    {
        $container = new Container();

        $this->assertFalse($container->bound('foo'));

        $container->bind('foo', function () {
            return 'bar';
        });

        $this->assertTrue($container->bound('foo'));
    }

    public function testBindingShared()
    {
        $container = new Container();

        $class = new stdClass();

        $container->singleton('class', function () use ($class) {
            return $class;
        });

        $this->assertSame($class, $container->make('class'));
    }

    public function testSlashesAreHandled()
    {
        $container = new Container();

        $container->bind('\Foo', function () {
            return 'bar';
        });

        $this->assertEquals('bar', $container->make('Foo'));
    }

    public function testParametersCanOverrideDependencies()
    {
        $container = new Container();

        $stub = new ContainerDependentTestStub(
            $mock = $this->createMock(ContainerStubInterface::class)
        );

        $resolved = $container->make(ContainerNestedDependentTestStub::class, [$stub]);

        $this->assertInstanceOf(ContainerNestedDependentTestStub::class, $resolved);

        $this->assertEquals($mock, $resolved->stub->implementation);
    }

    public function testContainerIsPassed()
    {
        $container = new Container();

        $container->bind('container', function (Container $container) {
            return $container;
        });

        $this->assertSame(
            $container,
            $container->make('container')
        );
    }

    public function testOverrideBindings()
    {
        $container = new Container();

        $container->bind('foo', function () {
            return 'bar';
        });

        $this->assertEquals('bar', $container->make('foo'));

        $container->bind('foo', function () {
            return 'baz';
        });

        $this->assertEquals('baz', $container->make('foo'));
    }

    public function testExtendedBindings()
    {
        $container = new Container();

        $container->bind('foo', function () {
            return 'foo';
        });

        $container->extend('foo', function ($old) {
            return $old.'bar';
        });

        $container->extend('foo', function ($old) {
            return $old.'baz';
        });

        $this->assertEquals('foobarbaz', $container->make('foo'));
    }

    public function testExtendIsLazyInitialized()
    {
        $container = new Container();

        $container->extend(ContainerLazyExtendTestStub::class, function (ContainerLazyExtendTestStub $object) {
            $object->init();

            return $object;
        });

        $this->assertFalse(ContainerLazyExtendTestStub::$initialized);

        $container->make(ContainerLazyExtendTestStub::class);

        $this->assertTrue(ContainerLazyExtendTestStub::$initialized);
    }

    public function testExtendCanBeCalledBeforeBind()
    {
        $container = new Container();

        $container->extend('foo', function ($old) {
            return $old.'bar';
        });

        $container->bind('foo', function () {
            return 'foo';
        });

        $this->assertEquals('foobar', $container->make('foo'));
    }

    public function testParametersCanBePassedThroughToClosure()
    {
        $container = new Container();

        $container->bind('foo', function (Container $container, $parameters) {
            return $parameters;
        });

        $this->assertEquals([1, 2, 3], $container->make('foo', [1, 2, 3]));
    }

    public function testResolutionOfDefaultParameters()
    {
        $container = new Container();

        $instance = $container->make(ContainerDefaultValueTestStub::class);

        $this->assertInstanceOf(ContainerTestStub::class, $instance->stub);

        $this->assertEquals('mark', $instance->name);
    }

    public function testResolvingCallbacksAreCalled()
    {
        $container = new Container();

        $container->resolving('foo', function ($object) {
            return $object->name = 'mark';
        });

        $container->bind('foo', function () {
            return new stdClass();
        });

        $instance = $container->make('foo');

        $this->assertEquals('mark', $instance->name);
    }

    public function testReboundListeners()
    {
        $test = new stdClass();
        $test->rebound = false;

        $container = new Container();

        $container->bind('foo', function () {
        });

        $container->rebinding('foo', function () use ($test) {
            $test->rebound = true;
        });

        $this->assertFalse($test->rebound);

        $container->bind('foo', function () {
        });

        $this->assertTrue($test->rebound);
    }

    public function testReboundListenersOnInstances()
    {
        $test = new stdClass();
        $test->rebound = false;

        $container = new Container();

        $container->instance('foo', function () {
        });

        $container->rebinding('foo', function () use ($test) {
            $test->rebound = true;
        });

        $container->instance('foo', function () {
        });

        $this->assertTrue($test->rebound);
    }

    public function testPassingSomePrimitiveParameters()
    {
        $container = new Container();

        $value = $container->make(ContainerMixedPrimitiveTestStub::class, [
            'first' => 'mark',
            'last' => 'topper',
        ]);

        $this->assertInstanceOf(ContainerMixedPrimitiveTestStub::class, $value);

        $this->assertEquals('mark', $value->first);

        $this->assertEquals('topper', $value->last);

        $this->assertInstanceOf(ContainerTestStub::class, $value->stub);

        $container = new Container();

        $value = $container->make(ContainerMixedPrimitiveTestStub::class, [
            0 => 'mark',
            2 => 'topper',
        ]);

        $this->assertInstanceOf(ContainerMixedPrimitiveTestStub::class, $value);

        $this->assertEquals('mark', $value->first);

        $this->assertEquals('topper', $value->last);

        $this->assertInstanceOf(ContainerTestStub::class, $value->stub);
    }

    public function testCreatingBoundConcreteClassPassesParameters()
    {
        $container = new Container();

        $container->bind('TestAbstractClass', ContainerConstructorParameterLoggingTestStub::class);

        $parameters = ['foo', 'bar'];

        $instance = $container->make('TestAbstractClass', $parameters);

        $this->assertInstanceOf(ContainerConstructorParameterLoggingTestStub::class, $instance);

        $this->assertEquals($parameters, $instance->receivedParameters);
    }

    /**
     * @expectedException Fist\Container\BindingException
     * @expectedExceptionMessage Unresolvable dependency resolving [Parameter #0 [ <required> $first ]] in class ContainerMixedPrimitiveTestStub
     */
    public function testInternalClassWithDefaultParameters()
    {
        $container = new Container();

        $container->make(ContainerMixedPrimitiveTestStub::class, []);
    }

    /**
     * @expectedException Fist\Container\BindingException
     * @expectedExceptionMessage Target [ContainerStubInterface] is not instantiable.
     */
    public function testBindingResolutionExceptionMessage()
    {
        $container = new Container();

        $container->make(ContainerStubInterface::class, []);
    }

    /**
     * @expectedException Fist\Container\BindingException
     * @expectedExceptionMessage Target [ContainerStubInterface] is not instantiable while building [ContainerTestContextInjectOne].
     */
    public function testBindingResolutionExceptionMessageIncludesBuildStack()
    {
        $container = new Container();

        $container->make(ContainerTestContextInjectOne::class, []);
    }

    public function testCallWithDependencies()
    {
        $container = new Container();

        $result = $container->call(function (stdClass $foo, $bar = []) {
            return func_get_args();
        });

        $this->assertInstanceOf(stdClass::class, $result[0]);

        $this->assertEquals([], $result[1]);

        $result = $container->call(function (stdClass $foo, $bar = []) {
            return func_get_args();
        }, ['bar' => 'mark']);

        $this->assertInstanceOf(stdClass::class, $result[0]);

        $this->assertEquals('mark', $result[1]);
    }

    /**
     * @expectedException ReflectionException
     */
    public function testCallWithAtSignBasedClassReferencesWithoutMethodThrowsException()
    {
        $container = new Container();

        $container->call(ContainerTestCallStub::class);
    }

    public function testCallWithAtSignBasedClassReferences()
    {
        $container = new Container();

        $result = $container->call('ContainerTestCallStub@work', ['foo', 'bar']);

        $this->assertEquals(['foo', 'bar'], $result);

        $container = new Container();

        $result = $container->call('ContainerTestCallStub@inject');

        $this->assertInstanceOf(ContainerTestStub::class, $result[0]);

        $this->assertEquals('mark', $result[1]);

        $container = new Container();

        $result = $container->call('ContainerTestCallStub@inject', ['default' => 'foo']);

        $this->assertInstanceOf(ContainerTestStub::class, $result[0]);

        $this->assertEquals('foo', $result[1]);

        $container = new Container();

        $result = $container->call(ContainerTestCallStub::class, ['foo', 'bar'], 'work');

        $this->assertEquals(['foo', 'bar'], $result);
    }

    public function testCallWithCallableArray()
    {
        $container = new Container();

        $stub = new ContainerTestCallStub();

        $result = $container->call([$stub, 'work'], ['foo', 'bar']);

        $this->assertEquals(['foo', 'bar'], $result);
    }

    public function testCallWithStaticMethodNameString()
    {
        $container = new Container();

        $result = $container->call('ContainerStaticMethodStub::inject');

        $this->assertInstanceOf(ContainerTestStub::class, $result[0]);

        $this->assertEquals('mark', $result[1]);
    }

    public function testCallWithGlobalMethodName()
    {
        $container = new Container();

        $result = $container->call('containerInjectTest');

        $this->assertInstanceOf(ContainerTestStub::class, $result[0]);

        $this->assertEquals('mark', $result[1]);
    }

    public function testContainerCanInjectDifferentImplementationsDependingOnContext()
    {
        $container = new Container();

        $container->bind(ContainerStubInterface::class, ContainerImplementationTestStub::class);

        $container->contextual(ContainerTestContextInjectOne::class, ContainerStubInterface::class, ContainerImplementationTestStub::class);
        $container->contextual(ContainerTestContextInjectTwo::class, ContainerStubInterface::class, ContainerImplementationTestStubTwo::class);

        $one = $container->make(ContainerTestContextInjectOne::class);
        $two = $container->make(ContainerTestContextInjectTwo::class);

        $this->assertInstanceOf(ContainerImplementationTestStub::class, $one->impl);
        $this->assertInstanceOf(ContainerImplementationTestStubTwo::class, $two->impl);
    }

    public function testContainerCanInjectDifferentImplementationsDependingOnContextWithClosure()
    {
        $container = new Container();

        $container->bind(ContainerStubInterface::class, ContainerImplementationTestStub::class);

        $container->contextual(ContainerTestContextInjectOne::class, ContainerStubInterface::class, ContainerImplementationTestStub::class);
        $container->contextual(ContainerTestContextInjectTwo::class, ContainerStubInterface::class, function (Container $container) {
            return $container->make(ContainerImplementationTestStubTwo::class);
        });

        $one = $container->make(ContainerTestContextInjectOne::class);
        $two = $container->make(ContainerTestContextInjectTwo::class);

        $this->assertInstanceOf(ContainerImplementationTestStub::class, $one->impl);
        $this->assertInstanceOf(ContainerImplementationTestStubTwo::class, $two->impl);
    }

    public function testContainerCanInjectSimpleVariable()
    {
        $container = new Container();

        $container->contextual(ContainerInjectVariableStub::class, '$something', 9000);

        $instance = $container->make('ContainerInjectVariableStub');

        $this->assertEquals(9000, $instance->something);

        $container = new Container();

        $container->contextual('ContainerInjectVariableStub', '$something', function (Container $container) {
            return $container->make('ContainerTestStub');
        });

        $instance = $container->make('ContainerInjectVariableStub');

        $this->assertInstanceOf('ContainerTestStub', $instance->something);
    }
}

interface ContainerStubInterface
{
    //
}

class ContainerTestStub
{
    //
}

class ContainerImplementationTestStub implements ContainerStubInterface
{
    //
}

class ContainerImplementationTestStubTwo implements ContainerStubInterface
{
    //
}

class ContainerDependentTestStub
{
    public $implementation;

    public function __construct(ContainerStubInterface $implementation)
    {
        $this->implementation = $implementation;
    }
}

class ContainerNestedDependentTestStub
{
    public $stub;

    public function __construct(ContainerDependentTestStub $stub)
    {
        $this->stub = $stub;
    }
}

class ContainerDefaultValueTestStub
{
    public $stub;

    public $name;

    public function __construct(ContainerTestStub $stub, $name = 'mark')
    {
        $this->stub = $stub;

        $this->name = $name;
    }
}

class ContainerMixedPrimitiveTestStub
{
    public $first;

    public $last;

    public $stub;

    public function __construct($first, ContainerTestStub $stub, $last = null)
    {
        $this->stub = $stub;

        $this->last = $last;

        $this->first = $first;
    }
}

class ContainerConstructorParameterLoggingTestStub
{
    public $receivedParameters;

    public function __construct($first, $second)
    {
        $this->receivedParameters = func_get_args();
    }
}

class ContainerLazyExtendTestStub
{
    public static $initialized = false;

    public function init()
    {
        static::$initialized = true;
    }
}

class ContainerTestCallStub
{
    public function work()
    {
        return func_get_args();
    }

    public function inject(ContainerTestStub $stub, $default = 'mark')
    {
        return func_get_args();
    }
}

class ContainerTestContextInjectOne
{
    public $impl;

    public function __construct(ContainerStubInterface $impl)
    {
        $this->impl = $impl;
    }
}

class ContainerTestContextInjectTwo
{
    public $impl;

    public function __construct(ContainerStubInterface $impl)
    {
        $this->impl = $impl;
    }
}

class ContainerStaticMethodStub
{
    public static function inject(ContainerTestStub $stub, $default = 'mark')
    {
        return func_get_args();
    }
}

class ContainerInjectVariableStub
{
    public $concrete;
    public $something;

    public function __construct(ContainerTestStub $concrete, $something)
    {
        $this->concrete = $concrete;
        $this->something = $something;
    }
}

function containerInjectTest(ContainerTestStub $stub, $default = 'mark')
{
    return func_get_args();
}
