<?php

namespace Fist\Routing;

use Closure;

class Route
{
    protected $methods = [];

    protected $uri;

    protected $pattern;

    protected $closure;

    protected $regex = [];

    public function __construct($methods, $uri, Closure $closure)
    {
        $this->methods = $methods;

        $this->uri = $uri;

        $this->closure = $closure;
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function hasMethod($method)
    {
        return in_array(strtolower($method), $this->methods);
    }

    public function matches($uri)
    {
        return (bool) preg_match("#{$this->generatePattern()}$#", $uri);
    }

    public function generatePattern()
    {
        if (is_null($this->pattern)) {
            $this->pattern = preg_replace_callback('~\{(.*?)\}~s', function($pattern) {
                $content = $pattern[1];

                if (strpos($content, ':')) {
                    $parts = explode(':', $content);

                    return '('.$parts[1].')';
                }

                if (isset($this->regex[$content])) {
                    return $this->regex[$content];
                }

                return '(.+)';
            }, $this->uri);
        }

        return $this->pattern;
    }

    public function where($name, $value)
    {
        $this->regex[$name] = $value;
    }

    public function callAction()
    {
        $action = $this->closure;

        return $action();
    }
}
