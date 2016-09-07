<?php

namespace Fist\Repository;

class ArrayRepository implements RepositoryInterface
{
    protected $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->items[$key];
        }

        return $default;
    }

    public function set($key, $value)
    {
        $this->items[$key] = $value;
    }

    public function has($key)
    {
        return isset($this->items[$key]);
    }
}
