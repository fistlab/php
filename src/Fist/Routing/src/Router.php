<?php

namespace Fist\Routing;

use Closure;

class Router
{
    protected $routes = [];

    public function add(array $methods, $uri, Closure $closure)
    {
        $methods = array_map('strtolower', $methods);

        $route = new Route($methods, $uri, $closure);

        $this->routes[] = $route;

        return $route;
    }

    public function get($uri, Closure $closure)
    {
        return $this->add(['get', 'head'], $uri, $closure);
    }

    public function post($uri, Closure $closure)
    {
        return $this->add(['post'], $uri, $closure);
    }

    public function put($uri, Closure $closure)
    {
        return $this->add(['put'], $uri, $closure);
    }

    public function patch($uri, Closure $closure)
    {
        return $this->add(['patch'], $uri, $closure);
    }

    public function delete($uri, Closure $closure)
    {
        return $this->add(['delete'], $uri, $closure);
    }

    public function head($uri, Closure $closure)
    {
        return $this->add(['head'], $uri, $closure);
    }

    public function connect($uri, Closure $closure)
    {
        return $this->add(['connect'], $uri, $closure);
    }

    public function options($uri, Closure $closure)
    {
        return $this->add(['options'], $uri, $closure);
    }

    public function trace($uri, Closure $closure)
    {
        return $this->add(['trace'], $uri, $closure);
    }

    public function any($uri, Closure $closure)
    {
        return $this->add(['get', 'post', 'put', 'patch', 'delete', 'head', 'connect', 'options', 'trace'], $uri, $closure);
    }

    protected function prepareQueryString($uri)
    {
        return rawurldecode(
            $this->stripQueryString($uri)
        );
    }

    protected function stripQueryString($uri)
    {
        // Strip query string (?foo=bar) and decode URI
        $pos = strpos($uri, '?');
        if ($pos !== false) {
            return substr($uri, 0, $pos);
        }

        return $uri;
    }

    public function dispatch($method = null, $uri = null)
    {
        if (is_null($method)) {
            $method = getenv('REQUEST_METHOD');
        }

        $method = strtolower($method);

        if (is_null($uri)) {
            $uri = getenv('REQUEST_URI');
        }

        $uri = $this->prepareQueryString($uri);

        $methodMismatch = false;

        foreach ($this->routes as $route) {
            if ($route->matches($uri)) {
                if ($route->hasMethod($method)) {
                    return $route->callAction();
                }

                $methodMismatch = true;
            }
        }

        if ($methodMismatch) {
            throw new MethodNotAllowedException('Method not allowed.');
        }

        throw new NotFoundException('Route not found.');
    }
}
