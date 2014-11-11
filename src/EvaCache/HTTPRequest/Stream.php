<?php

namespace Eva\EvaCache\HTTPRequest;


// +----------------------------------------------------------------------
// | [wallstreetcn]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14-9-26 14:33
// +----------------------------------------------------------------------
// + Stream.php
// +----------------------------------------------------------------------
use Eva\EvaEngine\IoC;
use Phalcon\Http\Client\Exception as HttpException;
use Phalcon\Http\Client\Header;
use Phalcon\Http\Client\Provider\Exception as ProviderException;
use Phalcon\Http\Client\Response;
use Phalcon\Http\Uri;

class Stream extends Request implements ProviderInterface
{
    protected $context = null;
    protected $lifetime = 0;
    protected $options = array();
    /**
     * @var \Phalcon\Cache\BackendInterface
     */
    protected $store;

    public static function isAvailable()
    {
        $wrappers = stream_get_wrappers();

        return in_array('http', $wrappers) && in_array('https', $wrappers);
    }

    public function __construct($lifetime)
    {
        if (!self::isAvailable()) {
            throw new ProviderException('HTTP or HTTPS stream wrappers not registered');
        }
        $this->lifetime = $lifetime;

        $this->context = stream_context_create();
        $this->initOptions();
        parent::__construct();
    }

    public function __destruct()
    {
        $this->context = null;
    }

    private function initOptions()
    {
        $this->setOptions(
            array(
                'user_agent' => 'Phalcon HTTP/' . self::VERSION . ' (Stream)',
                'follow_location' => 1,
                'max_redirects' => 20,
                'timeout' => 30
            )
        );
    }

    public function setOption($option, $value)
    {
        $this->options[] = array($option => $value);

        return stream_context_set_option($this->context, 'http', $option, $value);
    }

    public function setOptions($options)
    {
        $this->options[] = $options;
        return stream_context_set_option($this->context, array('http' => $options));
    }

    public function setTimeout($timeout)
    {
        $this->setOption('timeout', $timeout);
    }

    private function errorHandler($errno, $errstr)
    {
        throw new HttpException($errstr, $errno);
    }

    private function send($uri)
    {
        if (count($this->header) > 0) {
            $this->setOption('header', $this->header->build(Header::BUILD_FIELDS));
        }

        set_error_handler(array($this, 'errorHandler'));

        $cacheKey = '_curl_' . md5($uri->build() . serialize($this->options));
        $content = $this->store->get($cacheKey);
        if (!$content) {
            $content = file_get_contents($uri->build(), false, $this->context);
            restore_error_handler();
            $this->store->save($cacheKey, $content, $this->lifetime);
        }


        $response = new Response();
        $response->header->parse($http_response_header);
        $response->body = $content;

        return $response;
    }

    private function initPostFields($params)
    {
        if (!empty($params) && is_array($params)) {
            $this->header->set('Content-Type', 'application/x-www-form-urlencoded');
            $this->setOption('content', http_build_query($params));
        }
    }

    public function setProxy($host, $port = 8080, $user = null, $pass = null)
    {
        $uri = new Uri(
            array(
                'scheme' => 'tcp',
                'host' => $host,
                'port' => $port
            )
        );

        if (!empty($user)) {
            $uri->user = $user;
            if (!empty($pass)) {
                $uri->pass = $pass;
            }
        }

        $this->setOption('proxy', $uri->build());
    }

    public function get($uri, $params = array())
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(
            array(
                'method' => 'GET',
                'content' => ''
            )
        );

        $this->header->remove('Content-Type');

        return $this->send($uri);
    }

    public function head($uri, $params = array())
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(
            array(
                'method' => 'HEAD',
                'content' => ''
            )
        );

        $this->header->remove('Content-Type');

        return $this->send($uri);
    }

    public function delete($uri, $params = array())
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(
            array(
                'method' => 'DELETE',
                'content' => ''
            )
        );

        $this->header->remove('Content-Type');

        return $this->send($uri);
    }

    public function post($uri, $params = array(), $useEncoding = true)
    {
        $this->setOption('method', 'POST');

        $this->initPostFields($params);

        return $this->send($this->resolveUri($uri));
    }

    public function put($uri, $params = array(), $useEncoding = true)
    {
        $this->setOption('method', 'PUT');

        $this->initPostFields($params);

        return $this->send($this->resolveUri($uri));
    }

    /**
     * @param \Phalcon\Cache\BackendInterface $store
     */
    public function setStore($store)
    {
        $this->store = $store;
    }

    /**
     * @return \Phalcon\Cache\BackendInterface
     */
    public function getStore()
    {
        return $this->store;
    }
}
