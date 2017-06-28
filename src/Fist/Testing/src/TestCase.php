<?php

namespace Fist\Testing;

use Closure;
use Exception;
use PHPUnit_Framework_AssertionFailedError;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{
    public function throwsException(Closure $closure, $class = null, $messages = null)
    {
        try {
            $closure();

            $this->fail('Method did not throw exception as expected.');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            throw $e;
        } catch (Exception $e) {
            if (! is_null($class)) {
                $this->assertEquals($class, get_class($e));
            }

            if (is_array($messages)) {
                $this->assertTrue(in_array($e->getMessage(), $messages));
            } elseif (! is_null($messages)) {
                $this->assertEquals($messages, $e->getMessage());
            }
        }
    }
}
