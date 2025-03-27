<?php
/**
 * Amazon Login https://developer.amazon.com/docs/login-with-amazon/web-docs.html
 */
namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Exception\OAuthException;
use tinymeng\OAuth2\Helper\ConstCode;

class Amazon extends Gateway
{
    const API_BASE = 'https://api.amazon.com/';
    protected $AuthorizeURL = 'https://www.amazon.com/ap/oa';
    protected $AccessTokenURL = 'https://api.amazon.com/auth/o2/token';
    protected $UserInfoURL = 'https://api.amazon.com/user/profile';

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
            'scope'         => $this->config['scope'] ?: 'profile',
            'state'         => $this->config['state'],
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
    }

    /**
     * 获取当前授权用户的openid标识
     */
    public function openid()
    {
        $result = $this->getUserInfo();
        return $result['user_id'] ?? '';
    }

    /**
     * 获取格式化后的用户信息
     */
    public function userInfo()
    {
        $result = $this->getUserInfo();
        
        $userInfo = [
            'open_id'      => $result['user_id'] ?? '',
            'union_id'     => $result['user_id'] ?? '',
            'channel'      => ConstCode::TYPE_AMAZON,
            'nickname'     => $result['name'] ?? '',
            'gender'       => ConstCode::GENDER,
            'avatar'       => '',
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
        
        if (!isset($data['user_id'])) {
            throw new OAuthException('获取Amazon用户信息失败：' . json_encode($data));
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
                'client_id'     => $this->config['app_id'],
                'client_secret' => $this->config['app_secret'],
                'grant_type'    => $this->config['grant_type'],
                'code'          => isset($_REQUEST['code']) ? $_REQUEST['code'] : '',
                'redirect_uri'  => $this->config['callback'],
            ];
            $response = $this->post($this->AccessTokenURL, $params);
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
        throw new OAuthException('获取Amazon access_token 出错：' . json_encode($data));
    }
}