<?php

namespace Eva\EvaCache\Tag;

// +----------------------------------------------------------------------
// | [wallstreetcn]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14/11/3 22:09
// +----------------------------------------------------------------------
// + TaggedCache.php
// +----------------------------------------------------------------------

use Phalcon\Cache\BackendInterface;

class TaggedCache
{
    /**
     * The cache store implementation.
     *
     * @var BackendInterface
     */
    protected $store;
    /**
     * The tag set instance.
     *
     * @var TagSet
     */
    protected $tags;

    /**
     * Create a new tagged cache instance.
     *
     * @param  BackendInterface $store
     * @param  array $tags
     */
    public function __construct(BackendInterface $store, array $tags)
    {
        $this->tags = new TagSet($store, $tags);
        $this->store = $store;
    }

    /**
     * 通过键名获取一个缓存对象
     *
     * @param  string $key 键名
     * @param  \Closure $callback 当 key 不存在时返回并存储「闭包返回的值」
     * @param  int $lifetime 当指定了 $callback 参数时，该参数有效，用于设置闭包返回值的缓存存活时长，设置为负数时不缓存闭包返回值。
     * @return mixed
     */
    public function get($key, \Closure $callback = null, $lifetime = -1)
    {
        $key = $this->taggedItemKey($key);
        $value = $this->store->get($key);
        if (is_null($value) && is_callable($callback)) {
            $value = call_user_func($callback);
            if ($lifetime >= 0) {
                $this->store->save($key, $value, $lifetime);
            }
        }
        return $value;
    }

    /**
     * 保存缓存
     *
     * @param  string $key
     * @param  mixed $value
     * @param int $lifetime
     * @return void
     */
    public function save($key, $value, $lifetime)
    {
        if (!is_null($lifetime)) {
            $this->store->save($this->taggedItemKey($key), $value, $lifetime);
        }
    }


    /**
     * 从缓存中移除一个对象
     *
     * @param  string $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->store->delete($this->taggedItemKey($key));
    }

    /**
     * 清除所有包含 tag 列表中的 tag 的对象
     *
     * @return void
     */
    public function flush()
    {
        $this->tags->reset();
    }


    /**
     * 获取标签化的缓存对象的完整 key
     *
     * @param  string $key
     * @return string
     */
    public function taggedItemKey($key)
    {
        return sha1($this->tags->getNamespace()) . ':' . $key;
    }
}
