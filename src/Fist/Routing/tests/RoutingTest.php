<?php

use Fist\Routing\Route;
use Fist\Routing\Router;
use Fist\Testing\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class RoutingTest extends TestCase
{
    public function testBuildingRouter()
    {
        $router = new Router();

        $this->assertInstanceOf(Router::class, $router);
    }

    protected function addRoute(Router $router, $method, array $methods = null, $uri = 'foo', Closure $closure = null)
    {
        if (is_null($methods)) {
            if (strtolower($method) == 'get') {
                $methods = ['get', 'head'];
            } else {
                $methods = [$method];
            }
        }

        if (is_null($closure)) {
            $closure = function () {
                return 'foo';
            };
        }

        $route = $router->$method($uri, $closure);

        $this->assertEquals(array_map('strtolower', $methods), $route->getMethods());

        foreach ($methods as $m) {
            $this->assertTrue($route->hasMethod($m));
        }

        return $route;
    }

    public function testAddingGetRoute()
    {
        $this->addRoute(
            new Router(),
            'get'
        );
    }

    public function testAddingPostRoute()
    {
        $this->addRoute(
            new Router(),
            'post'
        );
    }

    public function testAddingPutRoute()
    {
        $this->addRoute(
            new Router(),
            'put'
        );
    }

    public function testAddingPatchRoute()
    {
        $this->addRoute(
            $router = new Router(),
            'patch'
        );
    }

    public function testAddingDeleteRoute()
    {
        $this->addRoute(
            new Router(),
            'delete'
        );
    }

    public function testAddingHeadRoute()
    {
        $this->addRoute(
            new Router(),
            'head'
        );
    }

    public function testAddingConnectRoute()
    {
        $this->addRoute(
            new Router(),
            'connect'
        );
    }

    public function testAddingOptionsRoute()
    {
        $this->addRoute(
            new Router(),
            'options'
        );
    }

    public function testAddingTraceRoute()
    {
        $this->addRoute(
            new Router(),
            'trace'
        );
    }

    public function testAddingAnyRoute()
    {
        $this->addRoute(
            new Router(),
            'any',
            ['get', 'post', 'put', 'patch', 'delete', 'head', 'connect', 'options', 'trace']
        );
    }

    public function testRouteMatches()
    {
        $this->addRoute(
            $router = new Router(),
            'get'
        );

        $match = $router->dispatch('get', 'http://localhost/foo');

        $this->assertEquals('foo', $match);
    }

    public function testRouteMatchesUppercaseMethods()
    {
        $this->addRoute(
            $router = new Router(),
            'get'
        );

        $match = $router->dispatch('GET', 'http://localhost/foo');

        $this->assertEquals('foo', $match);

        $this->addRoute(
            $router = new Router(),
            'GET'
        );

        $match = $router->dispatch('get', 'http://localhost/foo');

        $this->assertEquals('foo', $match);
    }

    /**
     * @expectedException Fist\Routing\NotFoundException
     * @expectedExceptionMessage Route not found.
     */
    public function testRouteFailsMatchingUri()
    {
        $this->addRoute(
            $router = new Router(),
            'get'
        );

        $router->dispatch('get', 'http://localhost/bar');
    }

    /**
     * @expectedException Fist\Routing\MethodNotAllowedException
     * @expectedExceptionMessage Method not allowed.
     */
    public function testRouteFailsMatchingMethod()
    {
        $this->addRoute(
            $router = new Router(),
            'get'
        );

        $router->dispatch('post', 'http://localhost/foo');
    }

    public function testRouteWithSlashes()
    {
        $router = new Router();

        $this->addRoute($router, 'get', null, 'foo/bar/baz');

        $this->assertEquals('foo', $router->dispatch('get', 'http://localhost/foo/bar/baz'));
    }

    public function testRouteWithSimpleRegex()
    {
        $router = new Router();

        $this->addRoute($router, 'get', null, 'users/(.+)');

        $this->assertEquals('foo', $router->dispatch('get', 'http://localhost/users/mark'));
    }

    public function testRouteWithNamedValues()
    {
        $router = new Router();

        $this->addRoute($router, 'get', null, 'users/{name}');

        $this->assertEquals('foo', $router->dispatch('get', 'http://localhost/users/mark'));
    }

    public function testRouteWithMultipleNamedValues()
    {
        $router = new Router();

        $this->addRoute($router, 'get', null, 'users/{name}/{id}');

        $this->assertEquals('foo', $router->dispatch('get', 'http://localhost/users/mark/1'));
    }

    public function testRouteWithNamedRegex()
    {
        $router = new Router();

        $this->addRoute($router, 'get', null, 'users/{name:[0-9]}');

        $this->assertEquals('foo', $router->dispatch('get', 'http://localhost/users/1'));
    }

    /**
     * @expectedException Fist\Routing\NotFoundException
     * @expectedExceptionMessage Route not found.
     */
    public function testRouteWithNamedRegexFailsSinceItAllowsOnlyNumbers()
    {
        $router = new Router();

        $this->addRoute($router, 'get', null, 'users/{name:[0-9]}');

        $this->assertEquals('foo', $router->dispatch('get', 'http://localhost/users/mark'));
    }

    public function testRouteWithWhereStatements()
    {
        $router = new Router();

        $this->addRoute($router, 'get', null, 'users/{name}')
            ->where('name', '[0-9]');

        $this->assertEquals('foo', $router->dispatch('get', 'http://localhost/users/1'));
    }

    /**
     * @expectedException Fist\Routing\NotFoundException
     * @expectedExceptionMessage Route not found.
     */
    public function testRouteWithWhereStatementsFailsUsingString()
    {
        $router = new Router();

        $this->addRoute($router, 'get', null, 'users/{name}')
            ->where('name', '[0-9]');

        $this->assertEquals('foo', $router->dispatch('get', 'http://localhost/users/mark'));
    }
}
