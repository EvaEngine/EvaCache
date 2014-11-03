<?php
namespace Eva\EvaCache\Tag;

// +----------------------------------------------------------------------
// | [wallstreetcn]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14/11/3 22:00
// +----------------------------------------------------------------------
// + TagSet.php
// +----------------------------------------------------------------------

use Phalcon\Cache\BackendInterface;

class TagSet
{
    /**
     * 缓存实例
     *
     * @var \Phalcon\Cache\BackendInterface;
     */
    protected $store;
    /**
     * tag 名数组
     *
     * @var array
     */
    protected $names = array();

    /**
     *
     * @param  BackendInterface $store
     * @param  array $names
     */
    public function __construct(BackendInterface $store, array $names = array())
    {
        $this->store = $store;
        $this->names = $names;
    }

    /**
     * 重置集合中所有的 tag
     *
     * @return void
     */
    public function reset()
    {
        array_walk($this->names, array($this, 'resetTag'));
    }

    /**
     * 获取给定的 tag 的唯一标示符
     *
     * @param  string $name
     * @return string
     */
    public function tagId($name)
    {
        return $this->store->get($this->tagKey($name)) ?: $this->resetTag($name);
    }

    /**
     * 获取集合中所有 tag 的唯一标示数组
     *
     * @return array
     */
    protected function tagIds()
    {
        return array_map(array($this, 'tagId'), $this->names);
    }

    /**
     * Get a unique namespace that changes when any of the tags are flushed.
     *
     * @return string
     */
    public function getNamespace()
    {
        return implode('|', $this->tagIds());
    }

    /**
     * 重置 tag 的标示符并返回新的 tag 标示符
     *
     * @param  string $name
     * @return string
     */
    public function resetTag($name)
    {
        $id = str_replace('.', '', uniqid('', true));
        $this->store->save($this->tagKey($name), $id, 0);
        return $id;
    }

    /**
     * 获取给定 tag 的唯一标示符键名
     *
     * @param  string $name
     * @return string
     */
    public function tagKey($name)
    {
        return 'tag:' . $name . ':key';
    }
}