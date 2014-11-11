<?php

namespace Eva\EvaCache\HTTPRequest;

// +----------------------------------------------------------------------
// | [wallstreetcn]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14-9-26 14:50
// +----------------------------------------------------------------------
// + ProviderInterface.php
// +----------------------------------------------------------------------

interface ProviderInterface extends RequestInterface
{

    public function __construct($lifetime);

    /**
     * @param \Phalcon\Cache\BackendInterface $store
     */
    public function setStore($store);

    /**
     * @return \Phalcon\Cache\BackendInterface
     */
    public function getStore();

    public static function isAvailable();


    public function setOption($option, $value);

    public function setOptions($options);

    public function setTimeout($timeout);

//    public function setConnectTimeout($timeout);


    public function setProxy($host, $port = 8080, $user = null, $pass = null);

    public function get($uri, $params = array());

    public function head($uri, $params = array());

    public function delete($uri, $params = array());

    public function post($uri, $params = array(), $useEncoding = true);

    public function put($uri, $params = array(), $useEncoding = true);
} 