# EvaCache
通用缓存模块，目前实现了 HTTPRequest 的缓存。
## HTTPRequest/CacheService
本缓存服务对 [phalcon/incubator](https://packagist.org/packages/phalcon/incubator) 进行了二次封装，可以在尽可能少地修改原来的代码
的情况下对 curl 操作加上缓存。

示例代码：
```php

use Eva\EvaCache\CacheManager;

$cacheManager = new CacheManager($this->getDI()->getGlobalCache());
$quotes = $cacheManager->http(
    function (ProviderInterface $provider) use ($self) {
        $provider->setBaseUri('http://api.markets.wallstreetcn.com/v1/');
        try {
            $response = $provider->get('quotes.json');
        } catch (\Exception $e) {
            return array();
        }
        if ($response->header->statusCode != 200) {
            return array();
        }
        return json_decode($response->body);
    },
    120 // 缓存有效期，单位是秒
);
if (!$quotes) {
    return $this->view->setVar('quotes', array());
}
$quotes = $quotes->results;
```
以下是使用 HTTPRequest/CacheService 之前的代码：

```php
$quotes = array();
$provider  = Request::getProvider();
$provider->setBaseUri('http://api.markets.wallstreetcn.com/v1/');
try {
    $response = $provider->get('quotes.json');
} catch (\Exception $e) {
    return $this->view->setVar('quotes', array());
}
if($response->header->statusCode != 200) {
    return $this->view->setVar('quotes', array());
}
$quotes = json_decode($response->body);
$quotes = $quotes->results;
```