<?php
/**
 * 企业微信开放平台 https://work.weixin.qq.com/api/doc/
 * api接口文档: https://work.weixin.qq.com/api/doc/90000/90135/91022
 */
namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Exception\OAuthException;
use tinymeng\OAuth2\Helper\ConstCode;

class Wecom extends Gateway
{
    const API_BASE = 'https://qyapi.weixin.qq.com/';
    protected $AuthorizeURL = 'https://open.work.weixin.qq.com/wwopen/sso/qrConnect';
    protected $AccessTokenURL = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken';
    protected $UserInfoURL = 'https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo';

    /**
     * 得到跳转地址
     */
    public function getRedirectUrl()
    {
        $params = [
            'appid'         => $this->config['app_id'],
            'agentid'       => $this->config['agent_id'],
            'redirect_uri'  => $this->config['callback'],
            'state'         => $this->config['state'] ?: '',
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
    }

    /**
     * 获取当前授权用户的openid标识
     */
    public function openid()
    {
        $result = $this->getUserInfo();
        return $result['userid'] ?? '';
    }

    /**
     * 获取格式化后的用户信息
     */
    public function userInfo()
    {
        $result = $this->getUserInfo();

        $userInfo = [
            'open_id'      => $result['userid'] ?? '',
            'union_id'     => '',
            'channel'      => ConstCode::TYPE_WECOM,
            'nickname'     => $result['userid'] ?? '',
            'gender'       => ConstCode::GENDER,
            'avatar'       => '',
            'type'         => ConstCode::getTypeConst(ConstCode::TYPE_WECOM, $this->type),
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
            'access_token' => $this->token['access_token'],
            'code'         => isset($_REQUEST['code']) ? $_REQUEST['code'] : '',
        ];
        
        $data = $this->get($this->UserInfoURL, $params);
        $data = json_decode($data, true);
        
        if (!isset($data['userid'])) {
            throw new OAuthException('获取企业微信用户信息失败：' . json_encode($data));
        }
        return $data;
    }

    /**
     * 获取access_token
     */
    protected function getAccessToken()
    {
        $params = [
            'corpid'     => $this->config['app_id'],
            'corpsecret' => $this->config['app_secret'],
        ];
        
        $response = $this->get($this->AccessTokenURL, $params);
        $response = json_decode($response, true);
        
        if (!isset($response['access_token'])) {
            throw new OAuthException('获取企业微信 access_token 出错：' . json_encode($response));
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
                'access_token' => $accessToken,
                'code'        => 'fake_code'
            ];
            $data = $this->get($this->UserInfoURL, $params);
            $data = json_decode($data, true);
            return $data['errcode'] === 40029;  // 无效的code但token有效
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 刷新AccessToken续期
     */
    public function refreshToken($refreshToken)
    {
        return $this->getAccessToken();
    }
}