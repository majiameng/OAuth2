<?php
/**
 * Yahoo OAuth2.0 https://developer.yahoo.com/oauth2/guide/
 */
namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Exception\OAuthException;
use tinymeng\OAuth2\Helper\ConstCode;

class Yahoo extends Gateway
{
    const API_BASE = 'https://api.login.yahoo.com/';
    protected $AuthorizeURL = 'https://api.login.yahoo.com/oauth2/request_auth';
    protected $AccessTokenURL = 'https://api.login.yahoo.com/oauth2/get_token';
    protected $UserInfoURL = 'https://api.login.yahoo.com/openid/v1/userinfo';

    /**
     * 得到跳转地址
     */
    public function getRedirectUrl()
    {
        $this->saveState();
        $params = [
            'client_id'     => $this->config['app_id'],
            'redirect_uri'  => $this->config['callback'],
            'response_type' => $this->config['response_type'],
            'scope'         => $this->config['scope'] ?: 'openid profile',
            'state'         => $this->config['state'],
            'nonce'         => md5(uniqid()),
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
    }

    /**
     * 获取当前授权用户的openid标识
     */
    public function openid()
    {
        $result = $this->getUserInfo();
        return $result['sub'] ?? '';
    }

    /**
     * 获取格式化后的用户信息
     */
    public function userInfo()
    {
        $result = $this->getUserInfo();
        
        $userInfo = [
            'open_id'      => $result['sub'] ?? '',
            'union_id'     => $result['sub'] ?? '',
            'channel'      => ConstCode::TYPE_YAHOO,
            'nickname'     => $result['name'] ?? '',
            'gender'       => ConstCode::GENDER,
            'avatar'       => $result['picture'] ?? '',
            'access_token' => $this->token['access_token'] ?? '',
            'native'       => $result
        ];
        return $userInfo;
    }

    /**
     * 获取原始接口返回的用户信息
     */
    public function getUserInfo()
    {
        $this->getToken();
        
        $headers = [
            'Authorization: Bearer ' . $this->token['access_token'],
        ];
        
        $data = $this->get($this->UserInfoURL, [], $headers);
        $data = json_decode($data, true);
        
        if (!isset($data['sub'])) {
            throw new OAuthException('获取Yahoo用户信息失败：' . json_encode($data));
        }
        return $data;
    }

    /**
     * 获取access_token
     */
    protected function getToken()
    {
        if (empty($this->token)) {
            $this->checkState();
            
            $params = [
                'grant_type'    => $this->config['grant_type'],
                'code'          => isset($_REQUEST['code']) ? $_REQUEST['code'] : '',
                'redirect_uri'  => $this->config['callback'],
            ];
            
            $headers = [
                'Authorization: Basic ' . base64_encode($this->config['app_id'] . ':' . $this->config['app_secret']),
                'Content-Type: application/x-www-form-urlencoded',
            ];
            
            $response = $this->post($this->AccessTokenURL, $params, $headers);
            $this->token = $this->parseToken($response);
        }
    }

    /**
     * 解析access_token方法请求后的返回值
     */
    protected function parseToken($token)
    {
        $data = json_decode($token, true);
        if (isset($data['access_token'])) {
            return $data;
        }
        throw new OAuthException('获取Yahoo access_token 出错：' . json_encode($data));
    }
}