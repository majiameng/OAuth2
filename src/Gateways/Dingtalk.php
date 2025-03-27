<?php
/**
 * 钉钉开放平台 https://open.dingtalk.com/
 * api接口文档: https://open.dingtalk.com/document/orgapp-server/obtain-identity-credentials
 */
namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Exception\OAuthException;
use tinymeng\OAuth2\Helper\ConstCode;

class Dingtalk extends Gateway
{
    const API_BASE = 'https://api.dingtalk.com/';
    protected $AuthorizeURL = 'https://login.dingtalk.com/oauth2/auth';
    protected $AccessTokenURL = 'https://api.dingtalk.com/v1.0/oauth2/userAccessToken';
    protected $UserInfoURL = 'https://api.dingtalk.com/v1.0/contact/users/me';

    /**
     * 得到跳转地址
     */
    public function getRedirectUrl()
    {
        $this->switchAccessTokenURL();
        $params = [
            'client_id'     => $this->config['app_id'],
            'redirect_uri'  => $this->config['callback'],
            'response_type' => $this->config['response_type'],
            'scope'         => $this->config['scope'] ?: 'openid',
            'state'         => $this->config['state'] ?: '',
            'prompt'        => 'consent'
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
    }

    /**
     * 获取当前授权用户的openid标识
     */
    public function openid()
    {
        $this->getToken();
        return $this->token['openId'] ?? '';
    }

    /**
     * 获取格式化后的用户信息
     */
    public function userInfo()
    {
        $result = $this->getUserInfo();

        $userInfo = [
            'open_id'      => $this->openid(),
            'union_id'     => $this->token['unionId'] ?? '',
            'channel'      => ConstCode::TYPE_DINGTALK,
            'nickname'     => $result['nick'] ?? '',
            'gender'       => ConstCode::GENDER,
            'avatar'       => $result['avatarUrl'] ?? '',
            'type'         => ConstCode::getTypeConst(ConstCode::TYPE_DINGTALK, $this->type),
            'access_token' => $this->token['accessToken'] ?? '',
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
            'x-acs-dingtalk-access-token' => $this->token['accessToken']
        ];
        
        $data = $this->get($this->UserInfoURL, [], $headers);
        return json_decode($data, true);
    }

    /**
     * 获取access_token
     */
    protected function getAccessToken()
    {
        $params = [
            'clientId'     => $this->config['app_id'],
            'clientSecret' => $this->config['app_secret'],
            'code'         => isset($_REQUEST['code']) ? $_REQUEST['code'] : '',
            'grantType'    => 'authorization_code'
        ];
        
        $response = $this->post($this->AccessTokenURL, $params);
        $response = json_decode($response, true);
        
        if (!isset($response['accessToken'])) {
            throw new OAuthException('获取钉钉 access_token 出错：' . json_encode($response));
        }
        return $response;
    }

    /**
     * 检验授权凭证AccessToken是否有效
     */
    public function validateAccessToken($accessToken = null)
    {
        try {
            $accessToken = $accessToken ?? $this->token['accessToken'];
            $headers = [
                'x-acs-dingtalk-access-token' => $accessToken
            ];
            $data = $this->get($this->UserInfoURL, [], $headers);
            $data = json_decode($data, true);
            return isset($data['nick']);
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
            'clientId'     => $this->config['app_id'],
            'clientSecret' => $this->config['app_secret'],
            'grantType'    => 'refresh_token',
            'refreshToken' => $refreshToken,
        ];
        
        $response = $this->post($this->AccessTokenURL, $params);
        $response = json_decode($response, true);
        
        if (isset($response['accessToken'])) {
            $this->token = $response;
            return true;
        }
        return false;
    }
}