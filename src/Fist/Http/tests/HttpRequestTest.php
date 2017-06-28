<?php

namespace FistTest;

use Fist\Http\Request;
use Fist\Testing\TestCase;
use Fist\Testing\WithSuperGlobals;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class HttpRequestTest extends TestCase
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
        $this->setGlobalServer('HTTP_X_ORIGINAL_URL', '/foo/bar/baz');

        $request = Request::createFromGlobals();
        $this->unsetGlobalServer('HTTP_X_ORIGINAL_URL');

        $this->assertTrue($request->is('foo/*/*'));
        $this->assertTrue($request->is('/foo/*/*'));
        $this->assertTrue($request->is('/foo/bar/*'));
        $this->assertTrue($request->is('/foo/bar/baz'));
        $this->assertTrue($request->is('/*/bar/baz'));
        $this->assertTrue($request->is('/*/*/baz'));
        $this->assertTrue($request->is('/*/*/*'));
        $this->assertTrue($request->is('/*/*+'));
        $this->assertTrue($request->is('/*+'));
        $this->assertFalse($request->is('/foo/*'));
        $this->assertFalse($request->is('/bar/*/*'));
    }

    public function testCheckingIfRequestMatchesRegexPattern()
    {
        $this->setGlobalServer('HTTP_X_ORIGINAL_URL', '/foo/bar/baz');

        $request = Request::createFromGlobals();
        $this->unsetGlobalServer('HTTP_X_ORIGINAL_URL');

        $this->assertTrue($request->matches('/foo/{.+}'));
        $this->assertFalse($request->matches('/bar/{.+}'));
    }

    public function testCehckingIfRequestIsResultOfPjax()
    {
        $request = Request::createFromGlobals();

        $this->assertFalse($request->isPjax());

        $request->getHeaders()->set('X-PJAX', true);

        $this->assertTrue($request->isPjax());
    }

    public function testCehckingIfRequestIsResultOfAjax()
    {
        $request = Request::createFromGlobals();

        $this->assertFalse($request->isAjax());

        // TODO: Set it to be AJAX

        $this->assertTrue($request->isAjax());
    }

    public function testGettingRequestSchema()
    {
        $request = Request::createFromGlobals();

        $this->assertEquals('http', $request->getSchema());

        $request->enableHttps();

        $this->assertEquals('https', $request->getSchema());
    }

    public function testGettingClientIp()
    {
        // TODO: Make this test
    }

    public function testGettingClientIps()
    {
        // TODO: Make this test
    }

    public function testGettingFingerprint()
    {
        $request = Request::createFromGlobals();

        $fingerprint = sha1(implode('|', array_merge(
            $route->methods(), [$route->domain(), $route->uri(), $this->ip()]
        )));

        $this->assertEquals($fingerprint, $request->getFingerprint());
    }
}
