# EvaCache
通用缓存模块，目前实现了 HTTPRequest 的缓存。
## HTTPRequest/CacheService
本缓存服务对 [phalcon/incubator](https://packagist.org/packages/phalcon/incubator) 进行了二次封装，可以在几乎不修改原来的代码
的情况下对 curl 操作加上缓存。

示例代码：
```php
use Eva\EvaCache\HTTPRequest\CacheService;
use Eva\EvaCache\HTTPRequest\ProviderInterface;

$quotes = array();
CacheService::execute(
    // 操作闭包，可以将原来的代码直接拷贝进来，不过需要注意的是变量作用域的问题。
    function (ProviderInterface $provider) use (&$quotes, $self) {
        $provider->setBaseUri('http://api.markets.wallstreetcn.com/v1/');
        try {
            $response = $provider->get('quotes.json');
        } catch (\Exception $e) {
            return $self->view->setVar('quotes', array());
        }
        if ($response->header->statusCode != 200) {
            return $self->view->setVar('quotes', array());
        }
        $quotes = json_decode($response->body);
    },
    // 缓存有效期，单位是秒
    120 
);
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