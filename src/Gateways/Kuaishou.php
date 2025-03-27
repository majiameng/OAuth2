<?php
/**
 * 快手开放平台 https://open.kuaishou.com/
 * api接口文档: https://open.kuaishou.com/platform/docs/api-overview
 */
namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Exception\OAuthException;
use tinymeng\OAuth2\Helper\ConstCode;

class Kuaishou extends Gateway
{
    const API_BASE = 'https://open.kuaishou.com/';
    protected $AuthorizeURL = 'https://open.kuaishou.com/oauth2/authorize';
    protected $AccessTokenURL = 'https://open.kuaishou.com/oauth2/access_token';
    protected $UserInfoURL = 'https://open.kuaishou.com/openapi/user_info';

    /**
     * 得到跳转地址
     */
    public function getRedirectUrl()
    {
        $this->switchAccessTokenURL();
        $params = [
            'app_id'        => $this->config['app_id'],
            'redirect_uri'  => $this->config['callback'],
            'response_type' => $this->config['response_type'],
            'scope'         => $this->config['scope'] ?: 'user_info',
            'state'         => $this->config['state'] ?: '',
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
            'channel'      => ConstCode::TYPE_KUAISHOU,
            'nickname'     => $result['name'] ?? '',
            'gender'       => $result['sex'] ?? ConstCode::GENDER,
            'avatar'       => $result['head'] ?? '',
            'type'         => ConstCode::getTypeConst(ConstCode::TYPE_KUAISHOU, $this->type),
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
            'app_id'       => $this->config['app_id'],
            'access_token' => $this->token['access_token'],
        ];
        
        $data = $this->get($this->UserInfoURL, $params);
        $data = json_decode($data, true);
        
        if (!isset($data['result'])) {
            throw new OAuthException('获取快手用户信息失败：' . json_encode($data));
        }
        return $data['result'];
    }

    /**
     * 获取access_token
     */
    protected function getAccessToken()
    {
        $params = [
            'app_id'       => $this->config['app_id'],
            'app_secret'   => $this->config['app_secret'],
            'code'         => isset($_REQUEST['code']) ? $_REQUEST['code'] : '',
            'grant_type'   => 'authorization_code',
        ];
        
        $response = $this->post($this->AccessTokenURL, $params);
        $response = json_decode($response, true);
        
        if (!isset($response['access_token'])) {
            throw new OAuthException('获取快手 access_token 出错：' . json_encode($response));
        }
        return $response;
    }

    /**
     * 检验授权凭证AccessToken是否有效
     */
    public function validateAccessToken($accessToken = null)
    {
        try {
            $accessToken = $accessToken ?? $this->token['access_token'];
            $params = [
                'app_id'       => $this->config['app_id'],
                'access_token' => $accessToken,
            ];
            $data = $this->get($this->UserInfoURL, $params);
            $data = json_decode($data, true);
            return isset($data['result']);
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
            'app_id'        => $this->config['app_id'],
            'app_secret'    => $this->config['app_secret'],
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
        ];
        
        $response = $this->post($this->AccessTokenURL, $params);
        $response = json_decode($response, true);
        
        if (isset($response['access_token'])) {
            $this->token = $response;
            return true;
        }
        return false;
    }
}