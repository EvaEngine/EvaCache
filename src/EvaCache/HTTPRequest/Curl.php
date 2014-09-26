<?php

namespace Eva\EvaCache\HTTPRequest;


// +----------------------------------------------------------------------
// | [wallstreetcn]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14-9-26 14:32
// +----------------------------------------------------------------------
// + Curl.php
// +----------------------------------------------------------------------
use Eva\EvaEngine\IoC;
use Phalcon\Http\Client\Exception as HttpException;
use Phalcon\Http\Client\Response;
use Phalcon\Http\Client\Provider\Exception as ProviderException;


class Curl extends Request implements ProviderInterface
{
    private $handle = null;
    protected $lifetime = 0;

    public static function isAvailable()
    {
        return false;
        return extension_loaded('curl');
    }

    public function __construct($lifetime)
    {
        if (!self::isAvailable()) {
            throw new ProviderException('CURL extension is not loaded');
        }
        $this->lifetime = $lifetime;
        $this->handle = curl_init();
        $this->initOptions();
        parent::__construct();
    }

    public function __destruct()
    {
        curl_close($this->handle);
    }

    public function __clone()
    {
        $request = new Curl($this->lifetime);
        $request->handle = curl_copy_handle($this->handle);

        return $request;
    }

    private function initOptions()
    {
        $this->setOptions(
            array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_AUTOREFERER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 20,
                CURLOPT_HEADER => true,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_USERAGENT => 'Phalcon HTTP/' . self::VERSION . ' (Curl)',
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_TIMEOUT => 30
            )
        );
    }

    public function setOption($option, $value)
    {
        return curl_setopt($this->handle, $option, $value);
    }

    public function setOptions($options)
    {
        return curl_setopt_array($this->handle, $options);
    }

    public function setTimeout($timeout)
    {
        $this->setOption(CURLOPT_TIMEOUT, $timeout);
    }

    public function setConnectTimeout($timeout)
    {
        $this->setOption(CURLOPT_CONNECTTIMEOUT, $timeout);
    }

    private function send()
    {
        $header = array();
        if (count($this->header) > 0) {
            $header = $this->header->build();
        }
        $header[] = 'Expect:';
        $this->setOption(CURLOPT_HTTPHEADER, $header);

        /** @var \Phalcon\Cache\Backend $globalCache */
        $globalCache = IoC::get('globalCache');
        $cacheKey = '_curl_' . md5(serialize(curl_getinfo($this->handle)));
        $content = $globalCache->get($cacheKey);
        if (!$content) {
            $content = curl_exec($this->handle);
            $globalCache->save($cacheKey, $content, $this->lifetime);
        }


        if ($errno = curl_errno($this->handle)) {
            throw new HttpException(curl_error($this->handle), $errno);
        }

        $headerSize = curl_getinfo($this->handle, CURLINFO_HEADER_SIZE);

        $response = new Response();
        $response->header->parse(substr($content, 0, $headerSize));
        $response->body = substr($content, $headerSize);

        return $response;
    }

    /**
     * Prepare data for a cURL post.
     *
     * @param mixed $params Data to send.
     * @param boolean $useEncoding Whether to url-encode params. Defaults to true.
     *
     * @return void
     */
    private function initPostFields($params, $useEncoding = true)
    {
        if (is_array($params)) {
            foreach ($params as $param) {
                if (is_string($param) && preg_match('/^@/', $param)) {
                    $useEncoding = false;
                    break;
                }
            }

            if ($useEncoding) {
                $params = http_build_query($params);
            }
        }

        if (!empty($params)) {
            $this->setOption(CURLOPT_POSTFIELDS, $params);
        }
    }

    public function setProxy($host, $port = 8080, $user = null, $pass = null)
    {
        $this->setOptions(
            array(
                CURLOPT_PROXY => $host,
                CURLOPT_PROXYPORT => $port
            )
        );

        if (!empty($user) && is_string($user)) {
            $pair = $user;
            if (!empty($pass) && is_string($pass)) {
                $pair .= ':' . $pass;
            }
            $this->setOption(CURLOPT_PROXYUSERPWD, $pair);
        }
    }

    public function get($uri, $params = array())
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(
            array(
                CURLOPT_URL => $uri->build(),
                CURLOPT_HTTPGET => true,
                CURLOPT_CUSTOMREQUEST => 'GET'
            )
        );

        return $this->send();
    }

    public function head($uri, $params = array())
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(
            array(
                CURLOPT_URL => $uri->build(),
                CURLOPT_HTTPGET => true,
                CURLOPT_CUSTOMREQUEST => 'HEAD'
            )
        );

        return $this->send();
    }

    public function delete($uri, $params = array())
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(
            array(
                CURLOPT_URL => $uri->build(),
                CURLOPT_HTTPGET => true,
                CURLOPT_CUSTOMREQUEST => 'DELETE'
            )
        );

        return $this->send();
    }

    public function post($uri, $params = array(), $useEncoding = true)
    {
        $this->setOptions(
            array(
                CURLOPT_URL => $this->resolveUri($uri),
                CURLOPT_POST => true,
                CURLOPT_CUSTOMREQUEST => 'POST'
            )
        );

        $this->initPostFields($params, $useEncoding);

        return $this->send();
    }

    public function put($uri, $params = array(), $useEncoding = true)
    {
        $this->setOptions(
            array(
                CURLOPT_URL => $this->resolveUri($uri),
                CURLOPT_POST => true,
                CURLOPT_CUSTOMREQUEST => 'PUT'
            )
        );

        $this->initPostFields($params, $useEncoding);

        return $this->send();
    }
}