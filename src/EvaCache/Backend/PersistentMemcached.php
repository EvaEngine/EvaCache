<?php

namespace Eva\EvaCache\Backend;

// +----------------------------------------------------------------------
// | [wallstreetcn]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 2014-11-06 10:53
// +----------------------------------------------------------------------
// + PersistentMemcached.php
// +----------------------------------------------------------------------

use Phalcon\Cache\Backend\Libmemcached;
use Phalcon\Cache\Exception;

class PersistentMemcached extends Libmemcached
{
    protected function _connect()
    {
        if (!isset($this->_options['servers']) || !is_array($this->_options['servers'])) {
            throw new Exception('Servers must be an array');
        }
        if (!$this->_options['connection_name']) {
            throw new Exception('connection_name field in options array is required in PersistentMemcached');

        }
        $this->_memcache = new \Memcached($this->_options['connection_name']);
        if (!$this->_memcache->addServers($this->_options['servers'])) {
            throw new Exception('Cannot connect to Memcached server');
        }
        if (isset($this->_options['client']) && is_array($this->_options['client'])) {
            $this->_memcache->setOptions($this->_options['client']);
        }
    }
}
