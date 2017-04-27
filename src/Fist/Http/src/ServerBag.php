<?php

namespace Fist\Http;

class ServerBag extends AbstractBag
{
    public function getHeaders()
    {
        $headers = [];
        $contentHeaders = [
            'CONTENT_LENGTH',
            'CONTENT_MD5',
            'CONTENT_TYPE',
        ];

        foreach ($this->items as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            }
            // CONTENT_* are not prefixed with HTTP_
            elseif (in_array($key, $contentHeaders)) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }
}
