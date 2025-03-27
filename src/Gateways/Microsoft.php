<?php
/**
 * Microsoft Identity Platform
 * https://docs.microsoft.com/en-us/azure/active-directory/develop/
 */
namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Exception\OAuthException;
use tinymeng\OAuth2\Helper\ConstCode;

class Microsoft extends Gateway
{
    const API_BASE = 'https://login.microsoftonline.com/';
    protected $AuthorizeURL = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
    protected $AccessTokenURL = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    protected $UserInfoURL = 'https://graph.microsoft.com/v1.0/me';

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
            'scope'         => $this->config['scope'] ?: 'User.Read',
            'state'         => $this->config['state'],
            'response_mode' => 'query',
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
    }

    /**
     * 获取当前授权用户的openid标识
     */
    public function openid()
    {
        $result = $this->getUserInfo();
        return $result['id'] ?? '';
    }

    /**
     * 获取格式化后的用户信息
     */
    public function userInfo()
    {
        $result = $this->getUserInfo();
        
        $userInfo = [
            'open_id'      => $result['id'] ?? '',
            'union_id'     => $result['id'] ?? '',
            'channel'      => ConstCode::TYPE_MICROSOFT,
            'nickname'     => $result['displayName'] ?? '',
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
        
        if (!isset($data['id'])) {
            throw new OAuthException('获取Microsoft用户信息失败：' . json_encode($data));
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
        throw new OAuthException('获取Microsoft access_token 出错：' . json_encode($data));
    }

    /**
     * 检验授权凭证AccessToken是否有效
     */
    public function validateAccessToken($accessToken = null)
    {
        try {
            $accessToken = $accessToken ?? $this->token['access_token'];
            $headers = [
                'Authorization: Bearer ' . $accessToken,
            ];
            $data = $this->get($this->UserInfoURL, [], $headers);
            $data = json_decode($data, true);
            return isset($data['id']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 刷新AccessToken续期
     */
    public function refreshToken($refreshToken)
    {
        $params = [
            'client_id'     => $this->config['app_id'],
            'client_secret' => $this->config['app_secret'],
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
            'redirect_uri'  => $this->config['callback'],
        ];
        
        $token = $this->post($this->AccessTokenURL, $params);
        $token = $this->parseToken($token);
        
        if (isset($token['access_token'])) {
            $this->token = $token;
            return true;
        }
        return false;
    }
}