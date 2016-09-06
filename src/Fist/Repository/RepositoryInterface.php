<?php

namespace Fist\Repository;

interface RepositoryInterface
{
    public function get($key, $default = null);

    public function set($key, $value);

    public function has($key);
}