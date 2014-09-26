<?php

namespace Eva\EvaCache\HTTPRequest;

// +----------------------------------------------------------------------
// | [wallstreetcn]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14-9-26 14:31
// +----------------------------------------------------------------------
// + Request.php
// +----------------------------------------------------------------------

use Phalcon\Http\Client\Provider\Exception as ProviderException;

class Request extends \Phalcon\Http\Client\Request
{
    public static function getCachedProvider($lifetime)
    {
        if (Curl::isAvailable()) {
            return new Curl($lifetime);
        }

        if (Stream::isAvailable()) {
            return new Stream($lifetime);
        }

        throw new ProviderException('There isn\'t any available provider');
    }
} 