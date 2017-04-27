<?php

use Fist\Http\Request;
use Fist\Testing\TestCase;

class RequestTest extends TestCase
{
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
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = Request::createFromGlobals();
        unset($_SERVER['REQUEST_METHOD']);

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
        $_SERVER['SERVER_NAME'] = 'foo.bar';
        $_SERVER['HTTP_X_ORIGINAL_URL'] = '/baz';

        $request = Request::createFromGlobals();
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['HTTP_X_ORIGINAL_URL']);

        $this->assertEquals('http://foo.bar/baz', $request->getUrl());
    }

    public function testGettingRequestUrlWithQuery()
    {
        $_SERVER['SERVER_NAME'] = 'foo.bar';
        $_SERVER['HTTP_X_ORIGINAL_URL'] = '/baz';
        $_SERVER['QUERY_STRING'] = 'foo=bar&bar=baz';

        $request = Request::createFromGlobals();
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['HTTP_X_ORIGINAL_URL']);
        unset($_SERVER['QUERY_STRING']);

        $this->assertEquals('http://foo.bar/baz?foo=bar&bar=baz', $request->getUrl());
    }

    public function testGettingRequestPath()
    {
        $_SERVER['SERVER_NAME'] = 'foo.bar';
        $_SERVER['HTTP_X_ORIGINAL_URL'] = '/baz';
        $_SERVER['QUERY_STRING'] = 'foo=bar&bar=baz';

        $request = Request::createFromGlobals();
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['HTTP_X_ORIGINAL_URL']);
        unset($_SERVER['QUERY_STRING']);

        $this->assertEquals('/baz', $request->getPath());
    }

    public function testGettingRequestSegments()
    {
        $_SERVER['HTTP_X_ORIGINAL_URL'] = '/foo/bar/baz';

        $request = Request::createFromGlobals();
        unset($_SERVER['HTTP_X_ORIGINAL_URL']);

        $this->assertEquals(['foo', 'bar', 'baz'], $request->segments());
    }

    public function testGettingRequestSegment()
    {
        $_SERVER['HTTP_X_ORIGINAL_URL'] = '/foo/bar/baz';

        $request = Request::createFromGlobals();
        unset($_SERVER['HTTP_X_ORIGINAL_URL']);

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
