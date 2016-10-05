<?php

namespace Fist\Testing;

use Closure;
use Exception;
use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase
{
    public function throwsException(Closure $closure, $class = null, $message = null)
    {
        try {
            $closure();

            $this->fail('Method did not throw exception as expected.');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            throw $e;
        } catch (Exception $e) {
            if (!is_null($class)) {
                $this->assertEquals($class, get_class($e));
            }

            if (!is_null($message)) {
                $this->assertEquals($message, $e->getMessage());
            }
        }
    }
}
