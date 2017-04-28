<?php

namespace Fist\Testing;

use Closure;
use Fist\Database\Database;

trait WithSuperGlobals
{
    protected function caseSuperGlobalKey($key)
    {
        return $key;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getGlobalServer($key, $default)
    {
        $key = $this->caseSuperGlobalKey($key);

        return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function hasGlobalServer($key, $default)
    {
        $key = $this->caseSuperGlobalKey($key);

        return isset($_SERVER[$key]);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function setGlobalServer($key, $value)
    {
        $key = $this->caseSuperGlobalKey($key);

        $_SERVER[$key] = $value;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function unsetGlobalServer($key, $value)
    {
        $key = $this->caseSuperGlobalKey($key);

        if ($this->hasGlobalServer($key)) {
            unset($_SERVER[$key]);
        }

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getGlobalQuery($key, $default)
    {
        $key = $this->caseSuperGlobalKey($key);

        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function hasGlobalQuery($key, $default)
    {
        $key = $this->caseSuperGlobalKey($key);

        return isset($_GET[$key]);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function setGlobalQuery($key, $value)
    {
        $key = $this->caseSuperGlobalKey($key);

        $_GET[$key] = $value;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function unsetGlobalQuery($key, $value)
    {
        $key = $this->caseSuperGlobalKey($key);

        if ($this->hasGlobalQuery($key)) {
            unset($_GET[$key]);
        }

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getGlobalParameter($key, $default)
    {
        $key = $this->caseSuperGlobalKey($key);

        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function hasGlobalParameter($key, $default)
    {
        $key = $this->caseSuperGlobalKey($key);

        return isset($_POST[$key]);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function setGlobalParameter($key, $value)
    {
        $key = $this->caseSuperGlobalKey($key);

        $_POST[$key] = $value;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function unsetGlobalParameter($key, $value)
    {
        $key = $this->caseSuperGlobalKey($key);

        if ($this->hasGlobalParameter($key)) {
            unset($_POST[$key]);
        }

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getGlobalCookie($key, $default)
    {
        $key = $this->caseSuperGlobalKey($key);

        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function hasGlobalCookie($key, $default)
    {
        $key = $this->caseSuperGlobalKey($key);

        return isset($_COOKIE[$key]);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function setGlobalCookie($key, $value)
    {
        $key = $this->caseSuperGlobalKey($key);

        $_COOKIE[$key] = $value;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function unsetGlobalCookie($key, $value)
    {
        $key = $this->caseSuperGlobalKey($key);

        if ($this->hasGlobalCookie($key)) {
            unset($_COOKIE[$key]);
        }

        return $this;
    }
}
