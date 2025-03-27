<?php
/**
 * Coding
 * api接口文档
*/
namespace tinymeng\OAuth2\Gateways;
use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Helper\ConstCode;

/**
 * Class Coding
 * @package tinymeng\OAuth2\Gateways
 * @Author: TinyMeng <666@majiameng.com>
 * @Created: 2023/07/09
 */
class Coding extends Gateway
{
    const API_BASE            = 'https://coding.net/api/';
    protected $AuthorizeURL   = 'https://coding.net/oauth_authorize.html';
    protected $AccessTokenURL = 'https://coding.net/api/oauth/access_token';
    protected $UserInfoURL    = 'https://coding.net/api/account/current_user';

    /**
     * Description:  得到跳转地址
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return string
     */
    public function getRedirectUrl()
    {
        //存储state
        $this->saveState();
        //登录参数
        $params = [
            'client_id'     => $this->config['app_id'],
            'redirect_uri'  => $this->config['callback'],
            'response_type' => $this->config['response_type'],
            'scope'         => $this->config['scope'],
            'state'         => $this->config['state'],
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
    }

    /**
     * Description:  获取格式化后的用户信息
     * @return array
     * @throws \Exception
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    public function userInfo()
    {
        $response = $this->getUserInfo();
        
        return [
            'openid'   => $response['id'] ?? '',
            'username' => $response['name'] ?? '',
            'avatar'   => $response['avatar'] ?? '',
            'email'    => $response['email'] ?? '',
            'name'     => $response['name'] ?? '',
        ];
    }

    /**
     * Description:  获取原始接口返回的用户信息
     * @return array
     * @throws \Exception
     */
    public function getUserInfo()
    {
        $this->openid();
        $data = $this->get($this->UserInfoURL, [
            'access_token' => $this->token['access_token']
        ]);
        $data = json_decode($data, true);
        
        if(!isset($data['id'])) {
            throw new \Exception("获取Coding用户信息失败：" . ($data['error_description'] ?? '未知错误'));
        }
        return $data;
    }

    /**
     * Description:  获取当前授权用户的openid标识
     * @return string
     * @throws \Exception
     */
    public function openid()
    {
        $this->getToken();
        return $this->token['uid'] ?? '';
    }

    /**
     * Description:  获取AccessToken
     * @throws \Exception
     */
    protected function getToken()
    {
        if (empty($this->token)) {
            $this->checkState();
            $params = [
                'grant_type'    => $this->config['grant_type'],
                'client_id'     => $this->config['app_id'],
                'client_secret' => $this->config['app_secret'],
                'code'          => isset($_REQUEST['code']) ? $_REQUEST['code'] : '',
                'redirect_uri'  => $this->config['callback'],
            ];
            $response = $this->post($this->AccessTokenURL, $params);
            $this->token = $this->parseToken($response);
        }
    }

    /**
     * Description:  解析access_token方法请求后的返回值
     * @param $token
     * @return mixed
     * @throws \Exception
     */
    protected function parseToken($token)
    {
        $data = json_decode($token, true);
        if (isset($data['access_token'])) {
            return $data;
        }
        throw new \Exception("获取Coding ACCESS_TOKEN出错：" . ($data['error_description'] ?? '未知错误'));
    }

    /**
     * 刷新AccessToken续期
     * @param string $refreshToken
     * @return bool
     * @throws \Exception
     */
    public function refreshToken($refreshToken)
    {
        $params = [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id'     => $this->config['app_id'],
            'client_secret' => $this->config['app_secret'],
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
