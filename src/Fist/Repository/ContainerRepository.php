<?php

namespace Fist\Repository;

use Closure;
use Fist\Container\Container;

class ContainerRepository implements RepositoryInterface
{
    protected $container;

    protected $prefix;

    public function __construct(Container $container, $prefix = null)
    {
        $this->container = $container;

        $this->prefix = $prefix;
    }

    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->container->make($this->prefix.$key);
        }

        return $default;
    }

    public function set($key, $value)
    {
        if ($value instanceof Closure) {
            // I'm still unsure whether or not to use 'singleton' over 'bind' in this ase.
            $this->container->bind($this->prefix.$key, $value);
        } else {
            $this->container->instance($this->prefix.$key, $value);
        }
    }

    public function has($key)
    {
        return $this->container->bound($this->prefix.$key);
    }
}
