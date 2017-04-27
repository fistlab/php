<?php

namespace Fist\Http;

abstract class AbstractBag
{
    protected $items;

    public function __construct(array $items)
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

    public function has($key)
    {
        return isset($this->items[$key]);
    }

    public function set($key, $value)
    {
        $this->items[$key] = $value;

        return $this;
    }

    public function all()
    {
        return $this->items;
    }
}
