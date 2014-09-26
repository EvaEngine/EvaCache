<?php

namespace Eva\EvaCache\HTTPRequest;

// +----------------------------------------------------------------------
// | [wallstreetcn]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14-9-26 14:54
// +----------------------------------------------------------------------
// + RequestInterface.php
// +----------------------------------------------------------------------

interface RequestInterface
{


    public static function getCachedProvider($lifetime);

    public function setBaseUri($baseUri);

    public function getBaseUri();

    public function resolveUri($uri);

} 