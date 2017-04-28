<?php

namespace FistTest;

use Fist\Http\Request;
use Fist\Testing\TestCase;
use Fist\Testing\WithSuperGlobals;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class RequestTest extends TestCase
{
    use WithSuperGlobals;

    public function testBuildingInstance()
    {
        $request = new Request();

        $this->assertInstanceOf(Request::class, $request);
    }

    public function testBuildingFromGlobals()
    {
        $request = Request::createFromGlobals();

        $this->assertInstanceOf(Request::class, $request);
    }

    public function testGettingRequestMethod()
    {
        $request = Request::createFromGlobals();

        $this->assertEquals('GET', $request->getMethod());
    }

    public function testGettingFakedRequestMethod()
    {
        $this->setGlobalServer('REQUEST_METHOD', 'POST');

        $request = Request::createFromGlobals();
        $this->unsetGlobalServer('REQUEST_METHOD');

        $this->assertEquals('POST', $request->getMethod());
    }

    public function testOverwritingRequestMethod()
    {
        $request = Request::createFromGlobals();

        $this->assertEquals('GET', $request->getMethod());

        $request->setMethod('PATCH');

        $this->assertEquals('PATCH', $request->getMethod());
    }

    public function testGettingRequestUrl()
    {
        $this->setGlobalServer('SERVER_NAME', 'foo.bar');
        $this->setGlobalServer('HTTP_X_ORIGINAL_URL', '/baz');

        $request = Request::createFromGlobals();
        $this->unsetGlobalServer('SERVER_NAME');
        $this->unsetGlobalServer('HTTP_X_ORIGINAL_URL');

        $this->assertEquals('http://foo.bar/baz', $request->getUrl());
    }

    public function testGettingRequestUrlWithQuery()
    {
        $this->setGlobalServer('SERVER_NAME', 'foo.bar');
        $this->setGlobalServer('HTTP_X_ORIGINAL_URL', '/baz');
        $this->setGlobalServer('QUERY_STRING', 'foo=bar&bar=baz');

        $request = Request::createFromGlobals();
        $this->unsetGlobalServer('SERVER_NAME');
        $this->unsetGlobalServer('HTTP_X_ORIGINAL_URL');
        $this->unsetGlobalServer('QUERY_STRING');

        $this->assertEquals('http://foo.bar/baz?foo=bar&bar=baz', $request->getUrl());
    }

    public function testGettingRequestPath()
    {
        $this->setGlobalServer('SERVER_NAME', 'foo.bar');
        $this->setGlobalServer('HTTP_X_ORIGINAL_URL', '/baz');
        $this->setGlobalServer('QUERY_STRING', 'foo=bar&bar=baz');

        $request = Request::createFromGlobals();
        $this->unsetGlobalServer('SERVER_NAME');
        $this->unsetGlobalServer('HTTP_X_ORIGINAL_URL');
        $this->unsetGlobalServer('QUERY_STRING');

        $this->assertEquals('/baz', $request->getPath());
    }

    public function testGettingRequestSegments()
    {
        $this->setGlobalServer('HTTP_X_ORIGINAL_URL', '/foo/bar/baz');

        $request = Request::createFromGlobals();
        $this->unsetGlobalServer('HTTP_X_ORIGINAL_URL');

        $this->assertEquals(['foo', 'bar', 'baz'], $request->segments());
    }

    public function testGettingRequestSegment()
    {
        $this->setGlobalServer('HTTP_X_ORIGINAL_URL', '/foo/bar/baz');

        $request = Request::createFromGlobals();
        $this->unsetGlobalServer('HTTP_X_ORIGINAL_URL');

        $this->assertNull($request->segment(0));
        $this->assertEquals('foo', $request->segment(1));
        $this->assertEquals('bar', $request->segment(2));
        $this->assertEquals('baz', $request->segment(3));
        $this->assertNull($request->segment(4));
    }

    public function testCheckingIfRequestMatchesPattern()
    {
    }

    public function testCheckingIfRequestMatchesFullPattern()
    {
    }

    public function testCehckingIfRequestIsResultOfPjax()
    {
    }

    public function testGettingRequestSchema()
    {
    }

    public function testGettingClientIp()
    {
    }

    public function testGettingClientIps()
    {
    }
}
