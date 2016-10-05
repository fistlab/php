<?php

namespace Fist\Repository;

class ArrayRepository implements RepositoryInterface
{
    protected $items = [];

    protected $separator;

    public function __construct(array $items = [], $separator = '.')
    {
        $this->items = $items;

        $this->separator = $separator;
    }

    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            if (!is_null($this->separator)) {
                return $this->getSeparated($key, $this->items);
            }

            return $this->items[$key];
        }

        return $default;
    }

    public function set($key, $value)
    {
        if (!is_null($this->separator)) {
            $this->setSeparated($key, $value, $this->items);
        } else {
            $this->items[$key] = $value;
        }
    }

    public function has($key)
    {
        if (!is_null($this->separator)) {
            return $this->hasSeparated($key, $this->items);
        }

        return isset($this->items[$key]);
    }

    protected function getSeparated($key, array &$items)
    {
        $parts = explode($this->separator, $key);
        $part = array_shift($parts);

        if (count($parts) == 0) {
            return $items[$part];
        }

        return $this->getSeparated(
            implode($this->separator, $parts),
            $items[$part]
        );
    }

    protected function setSeparated($key, $value, array &$items)
    {
        $parts = explode($this->separator, $key);
        $part = array_shift($parts);

        if (count($parts) == 0) {
            return $items[$part] = $value;
        }

        if (!isset($items[$part]) || !is_array($items[$part])) {
            $items[$part] = [];
        }

        return $this->setSeparated(
            implode($this->separator, $parts),
            $value,
            $items[$part]
        );
    }

    protected function hasSeparated($key, array $items)
    {
        $parts = explode($this->separator, $key);
        $part = array_shift($parts);

        if (!isset($items[$part]) || count($parts) == 0) {
            return isset($items[$part]);
        }

        return $this->hasSeparated(
            implode($this->separator, $parts),
            $items[$part]
        );
    }
}
