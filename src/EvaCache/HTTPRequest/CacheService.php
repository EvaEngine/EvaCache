<?php
namespace Eva\EvaCache\HTTPRequest;

// +----------------------------------------------------------------------
// | [wallstreetcn]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14-9-26 14:21
// +----------------------------------------------------------------------
// + HTTPRequestCache.php
// +----------------------------------------------------------------------


class CacheService
{
    /**
     * @param callable $callback
     * @param $lifetime
     * @throws \Phalcon\Http\Client\Provider\Exception
     */
    public static function execute(\Closure $callback, $lifetime)
    {
        $provider = Request::getCachedProvider($lifetime);
        call_user_func($callback, $provider);
    }
}