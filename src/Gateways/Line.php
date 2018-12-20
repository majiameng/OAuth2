<?php

namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Helper\ConstCode;
use tinymeng\OAuth2\Helper\Str;

class Line extends Gateway
{
    const API_BASE            = 'https://api.line.me/v2/';
    protected $AuthorizeURL   = 'https://access.line.me/oauth2/v2.1/authorize';
    protected $AccessTokenURL = 'https://api.line.me/oauth2/v2.1/token';

    /**
     * 得到跳转地址
     */
    public function getRedirectUrl()
    {
        $params = [
            'response_type' => $this->config['response_type'],
            'client_id'     => $this->config['app_id'],
            'redirect_uri'  => $this->config['callback'],
            'scope'         => $this->config['scope'],
            'state'         => $this->config['state'] ?: Str::random(),
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
    }

    /**
     * 获取当前授权用户的openid标识
     */
    public function openid()
    {
        $rsp = $this->getUserInfo();
        return $rsp['userId'];
    }

    /**
     * 获取格式化后的用户信息
     */
    public function userInfo()
    {
        $rsp = $this->getUserInfo();

        $userinfo = [
            'openid'  => $rsp['userId'],
            'channel' => ConstCode::TYPE_LINE,
            'nick'    => $rsp['displayName'],
            'gender'  => 'n', //line不返回性别信息
            'avatar'  => isset($rsp['pictureUrl']) ? $rsp['pictureUrl'] . '/large' : '',
        ];
        return $userinfo;
    }

    /**
     * 获取原始接口返回的用户信息
     */
    public function getUserInfo()
    {
        $this->getToken();

        $data = $this->call('profile', $this->token, 'GET');

        if (isset($data['error'])) {
            throw new \Exception($data['error_description']);
        }
        return $data;
    }

    /**
     * 发起请求
     *
     * @param string $api
     * @param array $params
     * @param string $method
     * @return array
     */
    private function call($api, $params = [], $method = 'GET')
    {
        $method  = strtoupper($method);
        $request = [
            'method' => $method,
            'uri'    => self::API_BASE . $api,
        ];

        $headers = ['Authorization' => $this->token['token_type'] . ' ' . $this->token['access_token']];

        $data = $this->$method($request['uri'], $params, $headers);

        return json_decode($data, true);
    }

    /**
     * 解析access_token方法请求后的返回值
     * @param string $token 获取access_token的方法的返回值
     */
    protected function parseToken($token)
    {
        $token = json_decode($token, true);
        if (isset($token['error'])) {
            throw new \Exception($token['error_description']);
        }
        return $token;
    }
}
