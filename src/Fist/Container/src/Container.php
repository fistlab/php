<?php

namespace Fist\Container;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;
use InvalidArgumentException;

class Container
{
    protected static $instance;
    protected $bindings = [];
    protected $instances = [];
    protected $extenders = [];
    protected $contextual = [];
    protected $rebindingListeners = [];
    protected $resolvingListeners = [];
    protected $buildStack = [];

    public function __construct()
    {
        if (is_null(static::$instance)) {
            static::$instance = $this;
        }
    }

    public static function getInstance()
    {
        return static::$instance;
    }

    public static function setInstance(Container $container)
    {
        static::$instance = $container;
    }

    public static function removeInstance()
    {
        static::$instance = null;
    }

    public function resolving($name, Closure $closure)
    {
        if (! isset($this->resolvingListeners[$name])) {
            $this->resolvingListeners[$name] = [];
        }

        $this->resolvingListeners[$name][] = $closure;
    }

    public function bind($name, $closure, $shared = false)
    {
        $name = $this->normalize($name);

        $bound = $this->bound($name);

        $this->bindings[$name] = [
            'concrete' => $closure,
            'shared' => $shared,
        ];

        if ($bound) {
            $this->rebound($name);
        }
    }

    protected function getCallReflector($callback)
    {
        if (is_string($callback) && strpos($callback, '::') !== false) {
            $callback = explode('::', $callback);
        }

        if (is_array($callback)) {
            return new ReflectionMethod($callback[0], $callback[1]);
        }

        return new ReflectionFunction($callback);
    }

    protected function addDependencyForCallParameter(ReflectionParameter $parameter, array &$parameters, &$dependencies)
    {
        if (array_key_exists($parameter->name, $parameters)) {
            $dependencies[] = $parameters[$parameter->name];

            unset($parameters[$parameter->name]);
        } elseif ($parameter->getClass()) {
            $dependencies[] = $this->make($parameter->getClass()->name);
        } elseif ($parameter->isDefaultValueAvailable()) {
            $dependencies[] = $parameter->getDefaultValue();
        }
    }

    protected function getMethodDependencies($callback, array $parameters = [])
    {
        $dependencies = [];

        foreach ($this->getCallReflector($callback)->getParameters() as $parameter) {
            $this->addDependencyForCallParameter($parameter, $parameters, $dependencies);
        }

        return array_merge($dependencies, $parameters);
    }

    protected function isCallableWithAtSign($callback)
    {
        return is_string($callback) && strpos($callback, '@') !== false;
    }

    protected function callClass($target, array $parameters = [], $defaultMethod = null)
    {
        $segments = explode('@', $target);

        $method = count($segments) == 2 ? $segments[1] : $defaultMethod;

        if (is_null($method)) {
            throw new InvalidArgumentException('Method not provided.');
        }

        return $this->call([$this->make($segments[0]), $method], $parameters);
    }

    public function call($callback, array $parameters = [], $defaultMethod = null)
    {
        if ($this->isCallableWithAtSign($callback) || $defaultMethod) {
            return $this->callClass($callback, $parameters, $defaultMethod);
        }

        $dependencies = $this->getMethodDependencies($callback, $parameters);

        return call_user_func_array($callback, $dependencies);
    }

    public function instance($name, $instance)
    {
        $name = $this->normalize($name);

        $bound = $this->bound($name);

        $this->instances[$name] = $instance;

        if ($bound) {
            $this->rebound($name);
        }
    }

    protected function rebound($name)
    {
        if (isset($this->rebindingListeners[$name])) {
            foreach ($this->rebindingListeners[$name] as $listener) {
                $listener();
            }
        }
    }

    public function rebinding($name, Closure $closure)
    {
        $name = $this->normalize($name);

        if (! isset($this->rebindingListeners[$name])) {
            $this->rebindingListeners[$name] = [];
        }

        $this->rebindingListeners[$name][] = $closure;
    }

    public function contextual($name, $needs, $give)
    {
        $name = $this->normalize($name);
        $needs = $this->normalize($needs);
        $give = $this->normalize($give);

        if (! isset($this->contextual[$name])) {
            $this->contextual[$name] = [];
        }

        $this->contextual[$name][$needs] = $give;
    }

    protected function getContextualConcrete($abstract)
    {
        $build = end($this->buildStack);

        if (isset($this->contextual[$build][$abstract])) {
            return $this->contextual[$build][$abstract];
        }
    }

    protected function keyParametersByArgument(array $dependencies, array $parameters)
    {
        foreach ($parameters as $key => $value) {
            if (is_numeric($key)) {
                unset($parameters[$key]);

                $parameters[$dependencies[$key]->name] = $value;
            }
        }

        return $parameters;
    }

    protected function getConcrete($name)
    {
        if (! is_null($concrete = $this->getContextualConcrete($name))) {
            return $concrete;
        }

        if (isset($this->bindings[$name])) {
            return $this->bindings[$name]['concrete'];
        }

        return $name;
    }

    public function make($name, array $parameters = [])
    {
        $name = $this->normalize($name);

        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        $concrete = $this->getConcrete($name);

        if ($this->isBuildable($concrete, $name)) {
            $object = $this->build($concrete, $parameters);
        } else {
            $object = $this->make($concrete, $parameters);
        }

        foreach ($this->getExtenders($name) as $extender) {
            $object = $extender($object, $this);
        }

        if ($this->isShared($name)) {
            $this->instances[$name] = $object;
        }

        $this->fireResolvingCallbacks($name, $object);

        return $object;
    }

    protected function fireResolvingCallbacks($name, $object)
    {
        if (isset($this->resolvingListeners[$name])) {
            foreach ($this->resolvingListeners[$name] as $listener) {
                $listener($object);
            }
        }
    }

    public function extend($name, Closure $closure)
    {
        $name = $this->normalize($name);

        if (isset($this->instances[$name])) {
            $this->instances[$name] = $closure($this->instances[$name], $this);

            $this->rebound($name);
        } else {
            $this->extenders[$name][] = $closure;
        }
    }

    protected function isBuildable($concrete, $abstract)
    {
        return $concrete === $abstract || $concrete instanceof Closure;
    }

    public function build($concrete, array $parameters = [])
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        $reflector = new ReflectionClass($concrete);

        if (! $reflector->isInstantiable()) {
            if (! empty($this->buildStack)) {
                $previous = implode(', ', $this->buildStack);

                $message = "Target [$concrete] is not instantiable while building [$previous].";
            } else {
                $message = "Target [$concrete] is not instantiable.";
            }

            throw new BindingException($message);
        }

        $this->buildStack[] = $concrete;

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            array_pop($this->buildStack);

            return new $concrete();
        }

        $dependencies = $constructor->getParameters();

        $parameters = $this->keyParametersByArgument(
            $dependencies, $parameters
        );

        $instances = $this->getDependencies(
            $dependencies, $parameters
        );

        array_pop($this->buildStack);

        return $reflector->newInstanceArgs($instances);
    }

    protected function getDependencies(array $parameters, array $primitives = [])
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();

            if (array_key_exists($parameter->name, $primitives)) {
                $dependencies[] = $primitives[$parameter->name];
            } elseif (is_null($dependency)) {
                $dependencies[] = $this->resolveNonClass($parameter);
            } else {
                $dependencies[] = $this->resolveClass($parameter);
            }
        }

        return $dependencies;
    }

    protected function resolveNonClass(ReflectionParameter $parameter)
    {
        if (! is_null($concrete = $this->getContextualConcrete('$'.$parameter->name))) {
            if ($concrete instanceof Closure) {
                return call_user_func($concrete, $this);
            } else {
                return $concrete;
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $message = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->name}";

        throw new BindingException($message);
    }

    public function getExtenders($name)
    {
        return isset($this->extenders[$name]) ? $this->extenders[$name] : [];
    }

    protected function resolveClass(ReflectionParameter $parameter)
    {
        try {
            return $this->make($parameter->getClass()->name);
        } catch (BindingException $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw $e;
        }
    }

    public function isShared($name)
    {
        return isset($this->bindings[$name]) && $this->bindings[$name]['shared'];
    }

    public function bound($name)
    {
        $name = $this->normalize($name);

        return isset($this->bindings[$name]) || isset($this->instances[$name]);
    }

    public function singleton($name, Closure $closure)
    {
        $this->bind($name, $closure, true);
    }

    public function normalize($name)
    {
        return is_string($name) ? ltrim($name, '\\') : $name;
    }
}
