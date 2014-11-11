<?php

namespace Eva\EvaCache;

// +----------------------------------------------------------------------
// | [wallstreetcn]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 2014-11-11 15:40
// +----------------------------------------------------------------------
// + CacheManager.php
// +----------------------------------------------------------------------

use Eva\EvaCache\HTTPRequest\Request;
use Eva\EvaCache\Tag\TaggedCache;
use Phalcon\Cache\BackendInterface;

class CacheManager
{
    /**
     * @var BackendInterface
     */
    protected $store;

    public function __construct(BackendInterface $store)
    {
        $this->store = $store;
    }

    /**
     * 获取缓存的值，如取值为空，则执行 $callback 并保存和返回回调函数返回的值。
     *
     * @param $key
     * @param callable $callback
     * @param $lifetime
     * @return mixed
     */
    public function getOrSave($key, \Closure $callback, $lifetime)
    {
        $value = $this->store->get($key);
        if (is_null($value) && is_callable($callback)) {
            $value = call_user_func($callback);
            $this->store->save($key, $value, $lifetime);
        }
        return $value;
    }

    /**
     * 执行可以缓存的 http 请求
     *
     * @param callable $callback 执行 http 请求的闭包，该闭包内会传入 Eva\EvaCache\HTTPRequest\ProviderInterface，将需要缓存的数据作为函数值返回即可。
     * @param int $lifetime 缓存生命周期，单位是秒
     * @return mixed
     * @throws \Phalcon\Http\Client\Provider\Exception
     */
    public function http(\Closure $callback, $lifetime)
    {
        $provider = Request::getCachedProvider($lifetime);
        $provider->setStore($this->store);
        return call_user_func($callback, $provider);
    }

    /**
     * 获取 TaggedCache 对象
     *
     * @param array|string $tags 标签，数组
     * @return TaggedCache
     */
    public function tags($tags)
    {
        return new TaggedCache($this->store, is_array($tags) ? $tags : func_get_args());
    }
}
