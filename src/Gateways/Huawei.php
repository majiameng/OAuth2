<?php
/**
 * 华为开发者联盟 https://developer.huawei.com/consumer/cn/doc/development/HMSCore-Guides/dev-process-0000001050123523
 * api接口文档: https://developer.huawei.com/consumer/cn/doc/development/HMSCore-References/server-api-api-0000001050123599
 */
namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Exception\OAuthException;
use tinymeng\OAuth2\Helper\ConstCode;

class Huawei extends Gateway
{
    const API_BASE = 'https://oauth-login.cloud.huawei.com/';
    protected $AuthorizeURL = 'https://oauth-login.cloud.huawei.com/oauth2/v3/authorize';
    protected $AccessTokenURL = 'https://oauth-login.cloud.huawei.com/oauth2/v3/token';
    protected $UserInfoURL = 'https://oauth-api.cloud.huawei.com/rest.php';

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
            'access_type'   => 'offline',
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
    }

    /**
     * 获取当前授权用户的openid标识
     */
    public function openid()
    {
        $this->getToken();
        return $this->token['open_id'] ?? '';
    }

    /**
     * 获取格式化后的用户信息
     */
    public function userInfo()
    {
        $result = $this->getUserInfo();
        
        $userInfo = [
            'open_id'      => $this->openid(),
            'union_id'     => $this->token['union_id'] ?? '',
            'channel'      => ConstCode::TYPE_HUAWEI,
            'nickname'     => $result['displayName'] ?? '',
            'gender'       => ConstCode::GENDER,
            'avatar'       => $result['headPictureURL'] ?? '',
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
        
        $params = [
            'method'       => 'oauth2/v3/userinfo',
            'access_token' => $this->token['access_token'],
            'nsp_ts'      => time(),
        ];
        
        $data = $this->get($this->UserInfoURL, $params);
        $data = json_decode($data, true);
        
        if (!isset($data['displayName'])) {
            throw new OAuthException('获取华为用户信息失败：' . json_encode($data));
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
     * 解析token
     */
    protected function parseToken($token)
    {
        $data = json_decode($token, true);
        if (isset($data['access_token'])) {
            return $data;
        }
        throw new OAuthException('获取华为 access_token 出错：' . json_encode($data));
    }

    /**
     * 检验授权凭证AccessToken是否有效
     */
    public function validateAccessToken($accessToken = null)
    {
        try {
            $accessToken = $accessToken ?? $this->token['access_token'];
            $params = [
                'method'       => 'oauth2/v3/tokeninfo',
                'access_token' => $accessToken,
                'nsp_ts'      => time(),
            ];
            $data = $this->get($this->UserInfoURL, $params);
            $data = json_decode($data, true);
            return isset($data['scope']);
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