<?php
namespace tinymeng\OAuth2\Connector;

use tinymeng\tools\HttpRequest;

trait GatewayTrait
{
    /**
     * @var 缓存处理
     */
    protected $cache;

    /**
     * Description:  执行GET请求操作
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $url
     * @param array $params
     * @param array $headers
     * @return string
     */
    protected function get($url, $params = [], $headers = [])
    {
        return HttpRequest::httpGet($url, $params,$headers);
    }

    /**
     * Description:  执行POST请求操作
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $url
     * @param array $params
     * @param array $headers
     * @return mixed
     */
    protected function post($url, $params = [], $headers = [])
    {
        $headers[] = 'Accept: application/json';//GitHub需要的header
        return HttpRequest::httpPost($url, $params,$headers);
    }


    public function setCache($cache)
    {
        $this->cache = $cache;
        return $this;
    }

    protected function getTokenFromCache($key)
    {
        return $this->cache ? $this->cache->get($key) : null;
    }

    protected function setTokenToCache($key, $token, $expires = 7200)
    {
        if ($this->cache) {
            $this->cache->set($key, $token, $expires);
        }
    }

}