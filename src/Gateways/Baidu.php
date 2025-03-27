<?php
/**
 * 百度
 * api接口文档
*/
namespace tinymeng\OAuth2\Gateways;
use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Exception\OAuthException;
use tinymeng\OAuth2\Helper\ConstCode;

/**
 * Class Baidu
 * @package tinymeng\OAuth2\Gateways
 * @Author: TinyMeng <666@majiameng.com>
 * @Created: 2023/07/09
 */
class Baidu extends Gateway
{
    const API_BASE            = 'https://openapi.baidu.com/';
    protected $AuthorizeURL   = 'https://openapi.baidu.com/oauth/2.0/authorize';
    protected $AccessTokenURL = 'https://openapi.baidu.com/oauth/2.0/token';
    protected $UserInfoURL = 'https://openapi.baidu.com/rest/2.0/passport/users/getLoggedInUser';

    public function __construct($config)
    {
        parent::__construct($config);
    }

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
            'response_type' => $this->config['response_type'],
            'client_id'     => $this->config['app_id'],
            'redirect_uri'  => $this->config['callback'],
            'state'         => $this->config['state'],
            'scope'         => $this->config['scope'],
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
    }

    /**
     * Description:  获取格式化后的用户信息
     * @return array
     * @throws OAuthException
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    public function userInfo()
    {
        $result = $this->getUserInfo();
        $userInfo = [
            'open_id' => isset($result['uid']) ? $result['uid'] : '',
            'union_id'=> isset($result['aid']) ? $result['aid'] : '',
            'channel' => ConstCode::TYPE_BAIDU,
            'nickname'=> $result['uname'] ?? $result['login_name'] ?? '',  // 优先使用 uname
            'gender'  => isset($result['sex']) ? (($result['sex'] == '男' ? 1 : ($result['sex'] == '女' ? 2 : 0))) : ConstCode::GENDER,
            'avatar'  => $result['portrait'] ?? '',  // 百度返回的头像字段
            'birthday'=> '',
            'access_token'=> $this->token['access_token'] ?? '',
            'native'=> $result,
        ];
        return $userInfo;
    }

    /**
     * Description:  获取原始接口返回的用户信息
     * @return array
     * @throws OAuthException
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    public function getUserInfo()
    {
        /** 获取用户信息 */
        $this->openid();

        $headers = ['Authorization: Bearer '.$this->token['access_token']];
        $data = $this->get($this->UserInfoURL, [], $headers);
        $data = json_decode($data, true);
        
        if(!isset($data['uid'])) {
            throw new OAuthException("获取百度用户信息失败：" . ($data['error_description'] ?? '未知错误'));
        }
        return $data;
    }

    /**
     * Description:  获取当前授权用户的openid标识
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return string
     * @throws OAuthException
     */
    public function openid()
    {
        $this->getToken();
        return $this->token['uid'] ?? '';
    }


    /**
     * Description:  获取AccessToken
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    protected function getToken(){
        if (empty($this->token)) {
            /** 验证state参数 */
            $this->CheckState();

            /** 获取参数 */
            $params = $this->accessTokenParams();

            /** 获取access_token */
            $this->AccessTokenURL = $this->AccessTokenURL . '?' . http_build_query($params);
            $token =  $this->post($this->AccessTokenURL);
            /** 解析token值(子类实现此方法) */
            $this->token = $this->parseToken($token);
        }
    }

    /**
     * Description:  解析access_token方法请求后的返回值
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $token
     * @return mixed
     * @throws OAuthException
     */
    protected function parseToken($token)
    {
        $data = json_decode($token, true);
        if (isset($data['access_token'])) {
            return $data;
        } else {
            throw new OAuthException("获取Baidu ACCESS_TOKEN出错：{$data['error']}");
        }
    }


    /**
     * 刷新AccessToken续期
     * @param string $refreshToken
     * @return bool
     * @throws OAuthException
     */
    public function refreshToken($refreshToken)
    {
        $params = [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id'     => $this->config['app_id'],
            'client_secret' => $this->config['app_secret'],
        ];
        
        $this->AccessTokenURL = static::API_BASE . 'oauth/2.0/token';
        $token = $this->post($this->AccessTokenURL . '?' . http_build_query($params));
        $token = $this->parseToken($token);
        
        if (isset($token['access_token'])) {
            $this->token = $token;
            return true;
        }
        return false;
    }

    /**
     * 检验授权凭证AccessToken是否有效
     * @param string $accessToken
     * @return bool
     */
    public function validateAccessToken($accessToken = null)
    {
        try {
            $accessToken = $accessToken ?? $this->token['access_token'];
            $headers = ['Authorization: Bearer ' . $accessToken];
            $data = $this->get($this->UserInfoURL, [], $headers);
            $data = json_decode($data, true);
            return isset($data['uid']);
        } catch (OAuthException $e) {
            return false;
        }
    }
}
