<?php

namespace Fist\Facade;

use Fist\Container\Container;

abstract class ContainerFacade extends Facade implements ContainerFacadeInterface
{
    public static function getFacadeInstance()
    {
        return static::getContainerInstance()
            ->make(static::getFacadeAccessor());
    }

    public static function getContainerInstance()
    {
        return Container::getInstance();
    }
}