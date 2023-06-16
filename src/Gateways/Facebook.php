<?php
/**
 * fackbook 控制台 https://developers.facebook.com/apps/
 * Oauth开发文档:
 *      https://developers.facebook.com/docs/facebook-login/
 * 1.创建项目->并创建凭证
 * 2.补充基本信息
 * https://developers.facebook.com/apps/你的appid/settings/basic/#add-platform-button
 * 3.获取用户信息user_gender权限
 * https://developers.facebook.com/apps/你的appid/app-review/permissions/
 * 申请这个权限user_gender
 */
namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Helper\ConstCode;
use tinymeng\OAuth2\Helper\Str;

/**
 * Class Facebook
 * @package tinymeng\OAuth2\Gateways
 * @Author: TinyMeng <666@majiameng.com>
 * @Created: 2020/7/29
 */
class Facebook extends Gateway
{
    const API_BASE            = 'https://graph.facebook.com/v3.1/';
    protected $AuthorizeURL   = 'https://www.facebook.com/v3.1/dialog/oauth';
    protected $AccessTokenURL = 'https://graph.facebook.com/v3.1/oauth/access_token';

    /**
     * 得到跳转地址
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
            'scope'         => $this->config['scope'],
            'state'         => $this->config['state'] ?: Str::random(),
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
    }

    /**
     * 获取当前授权用户的openid标识
     */
    public function openid()
    {
        $userInfo = $this->userInfo();
        return $userInfo['openid'];
    }

    /**
     * 获取格式化后的用户信息
     */
    public function userInfo()
    {
        $result = $this->getUserInfo();

        $userInfo = [
            'open_id'  => $result['id'],
            'access_token'=> isset($this->token['access_token']) ? $this->token['access_token'] : '',
            'union_id'=> $result['id'],
            'channel' => ConstCode::TYPE_FACEBOOK,
            'nickname'    => $result['name'],
            'gender'  => isset($result['gender']) ? $this->getGender($result['gender']) : ConstCode::GENDER,
            'avatar'  => $this->getAvatar($result),
        ];
        return $userInfo;
    }

    /**
     * 获取原始接口返回的用户信息
     */
    public function getUserInfo()
    {
        if($this->type == 'app'){//App登录
            if(!isset($_REQUEST['access_token']) ){
                throw new \Exception("Facebook APP登录 需要传输access_token参数! ");
            }
            $this->token['access_token'] = $_REQUEST['access_token'];
        }else {
            $this->getToken();
        }
        $fields = isset($this->config['fields']) ? $this->config['fields'] : 'id,name,gender,picture.width(400)';
        return $this->call('me', ['access_token' => $this->token['access_token'], 'fields' => $fields], 'GET');
    }

    /**
     * 发起请求
     *
     * @param string $api
     * @param array $params
     * @param string $method
     * @return array
     */
    private function call($api, $params = [], $method = 'GET')
    {
        $method  = strtoupper($method);
        $request = [
            'method' => $method,
            'uri'    => self::API_BASE . $api,
        ];

        $data = $this->$method($request['uri'], $params);

        return json_decode($data, true);
    }

    /**
     * Description:  重写 获取的AccessToken请求参数
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return array
     */
    protected function accessTokenParams()
    {
        $params = [
            'client_id'     => $this->config['app_id'],
            'client_secret' => $this->config['app_secret'],
            'code'          => isset($_REQUEST['code']) ? $_REQUEST['code'] : '',
            'redirect_uri'  => $this->config['callback'],
        ];
        return $params;
    }

    /**
     * 解析access_token方法请求后的返回值
     * @param string $token 获取access_token的方法的返回值
     */
    protected function parseToken($token)
    {
        $token = json_decode($token, true);
        if (isset($token['error'])) {
            throw new \Exception($token['error']['message']);
        }
        return $token;
    }

    /**
     * 获取用户头像
     *
     * @param array $result
     * @return string
     */
    private function getAvatar($result)
    {
        if (isset($result['picture']['data']['url'])) {
            return $result['picture']['data']['url'];
        }
        return '';
    }
}
