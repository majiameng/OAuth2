<?php
/**
 * 小米开放平台 https://dev.mi.com/console/doc/detail?pId=711
 * api接口文档: https://dev.mi.com/console/doc/detail?pId=707
 */
namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Exception\OAuthException;
use tinymeng\OAuth2\Helper\ConstCode;

class Xiaomi extends Gateway
{
    const API_BASE = 'https://open.account.xiaomi.com/';
    protected $AuthorizeURL = 'https://account.xiaomi.com/oauth2/authorize';
    protected $AccessTokenURL = 'https://account.xiaomi.com/oauth2/token';
    protected $UserInfoURL = 'https://open.account.xiaomi.com/user/profile';

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
            'skip_confirm'  => 1,
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
    }

    /**
     * 获取当前授权用户的openid标识
     */
    public function openid()
    {
        $this->getToken();
        return $this->token['user_id'] ?? '';
    }

    /**
     * 获取格式化后的用户信息
     */
    public function userInfo()
    {
        $result = $this->getUserInfo();
        
        $userInfo = [
            'open_id'      => $this->openid(),
            'union_id'     => $this->token['user_id'] ?? '',
            'channel'      => ConstCode::TYPE_XIAOMI,
            'nickname'     => $result['miliaoNick'] ?? '',
            'gender'       => $this->getGender($result['sex'] ?? ''),
            'avatar'       => $result['miliaoIcon'] ?? '',
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
            'clientId'     => $this->config['app_id'],
            'token'        => $this->token['access_token'],
        ];
        
        $data = $this->get($this->UserInfoURL, $params);
        $data = json_decode($data, true);
        
        if (!isset($data['result']) || $data['result'] !== 'ok') {
            throw new OAuthException('获取小米用户信息失败：' . json_encode($data));
        }
        return $data['data'];
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
        throw new OAuthException('获取小米 access_token 出错：' . json_encode($data));
    }

    /**
     * 获取性别
     */
    private function getGender($gender)
    {
        $map = [
            '1' => ConstCode::GENDER_MAN,
            '0' => ConstCode::GENDER_WOMEN,
        ];
        return $map[$gender] ?? ConstCode::GENDER;
    }

    /**
     * 检验授权凭证AccessToken是否有效
     */
    public function validateAccessToken($accessToken = null)
    {
        try {
            $accessToken = $accessToken ?? $this->token['access_token'];
            $params = [
                'clientId' => $this->config['app_id'],
                'token'    => $accessToken,
            ];
            $data = $this->get($this->UserInfoURL, $params);
            $data = json_decode($data, true);
            return isset($data['result']) && $data['result'] === 'ok';
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