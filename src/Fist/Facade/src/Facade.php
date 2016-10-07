<?php

namespace Fist\Facade;

abstract class Facade implements FacadeInterface
{
    public static function __callStatic($method, array $arguments = [])
    {
        return static::callFacadeInstance($method, $arguments);
    }

    public static function callFacadeInstance($method, array $arguments = [])
    {
        $instance = static::getFacadeInstance();

        if (!method_exists($instance, $method)) {
            throw new InvalidArgumentException("Method [{$method}] does not exists.");
        }

        return call_user_func_array([$instance, $method], $arguments);
    }
}