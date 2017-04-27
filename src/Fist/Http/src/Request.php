<?php

namespace Fist\Http;

class Request
{
    protected $server;

    protected $baseUrl;

    protected $path;

    protected $requestUri;

    public function __construct(array $query = [], array $request = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->initialize($query, $request, $cookies, $files, $server, $content);
    }

    public static function createFromGlobals()
    {
        return new static($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
    }

    public function initialize(array $query = [], array $request = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->query = new ParameterBag($query);
        $this->request = new ParameterBag($request);
        $this->cookies = new ParameterBag($cookies);
        $this->files = new FileBag($files);
        $this->server = new ServerBag($server);
        $this->cookies = new CookieBag($cookies);
        $this->headers = new HeaderBag($this->server->getHeaders());
    }

    public function getMethod()
    {
        return $this->server->get('REQUEST_METHOD', 'GET');
    }

    public function setMethod($method)
    {
        $this->server->set('REQUEST_METHOD', $method);

        return $this;
    }

    public function getUrl()
    {
        if (null !== $queryString = $this->getQueryString()) {
            $queryString = '?'.$queryString;
        }

        return $this->getSchemaAndHttpHost().$this->getBaseUrl().$this->getPath().$queryString;
    }

    public function getQueryString()
    {
        return $this->server->get('QUERY_STRING');
    }

    public function setQueryString($queryString)
    {
        $this->server->set('QUERY_STRING', $queryString);

        return $this;
    }

    public function getSchemaAndHttpHost()
    {
        return implode('://', [
            $this->getSchema(),
            $this->getHttpHost(),
        ]);
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function getSchema()
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    public function isSecure()
    {
        return $this->server->get('HTTPS') == 'on';
    }

    public function enableHttps()
    {
        return $this->setHttps('on');
    }

    public function disableHttps()
    {
        return $this->setHttps('off');
    }

    public function setHttps($https)
    {
        $this->server->set('HTTPS', $https);

        return $this;
    }

    public function getHttpHost()
    {
        $schema = $this->getSchema();
        $port = $this->getPort();

        if (('http' == $schema && $port == 80) || ('https' == $schema && $port == 443)) {
            return $this->getHost();
        }

        return $this->getHost().':'.$port;
    }

    public function getHost()
    {
        return $this->server->get('SERVER_NAME', '');
    }

    public function setHost($host)
    {
        $this->server->set('SERVER_NAME', $host);

        return $this;
    }

    public function getPort()
    {
        return $this->server->get('SERVER_PORT', 80);
    }

    public function setPort($port)
    {
        $this->server->set('SERVER_PORT', $port);

        return $this;
    }

    public function getPath()
    {
        if (is_null($this->path)) {
            $this->path = $this->preparePath();
        }

        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function preparePath()
    {
        $baseUrl = $this->getBaseUrl();

        if (is_null($requestUri = $this->getRequestUri())) {
            return '/';
        }

        // Remove the query string from REQUEST_URI
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        $path = substr($requestUri, strlen($baseUrl));

        if (is_null($baseUrl)) {
            return $requestUri;
        } elseif (! $path) {
            return '/';
        }

        return $path;
    }

    public function getRequestUri()
    {
        if (is_null($this->requestUri)) {
            $this->requestUri = $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    public function setRequestUri($requestUri)
    {
        $this->requestUri = $requestUri;

        return $this;
    }

    public function prepareRequestUri()
    {
        if ($this->headers->has('X_ORIGINAL_URL')) {
            // IIS with Microsoft Rewrite Module
            return $this->headers->get('X_ORIGINAL_URL');
        } elseif ($this->headers->has('X_REWRITE_URL')) {
            // IIS with ISAPI_Rewrite
            return $this->headers->get('X_REWRITE_URL');
        } elseif ($this->server->get('IIS_WasUrlRewritten') == '1' && ! empty($this->server->get('UNENCODED_URL'))) {
            // IIS7 with URL Rewrite: make sure we get the unencoded URL (double slash problem)
            return $this->server->get('UNENCODED_URL');
        } elseif ($this->server->has('REQUEST_URI')) {
            $requestUri = $this->server->get('REQUEST_URI');

            // HTTP proxy reqs setup request URI with scheme and host [and port] + the URL path, only use URL path
            $schemeAndHttpHost = $this->getSchemeAndHttpHost();
            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                return substr($requestUri, strlen($schemeAndHttpHost));
            }

            return $requestUri;
        } elseif ($this->server->has('ORIG_PATH_INFO')) {
            // IIS 5.0, PHP as CGI
            $requestUri = $this->server->get('ORIG_PATH_INFO');

            if (! empty($queryString = $this->server->get('QUERY_STRING'))) {
                return $requestUri.'?'.$queryString;
            }

            return $requestUri;
        }

        return '';
    }

    public function segments()
    {
        $requestUri = $this->getRequestUri();

        // Remove leading slash
        if (substr($requestUri, 0, 1) == '/') {
            $requestUri = substr($requestUri, 1);
        }

        return explode('/', $requestUri);
    }

    public function segment($i)
    {
        $i--;
        $segments = $this->segments();

        return isset($segments[$i]) ? $segments[$i] : null;
    }
}
