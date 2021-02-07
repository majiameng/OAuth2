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
        $userinfo = $this->userInfo();
        return $userinfo['openid'];
    }

    /**
     * 获取格式化后的用户信息
     */
    public function userInfo()
    {
        $rsp = $this->getUserInfo();

        $userinfo = [
            'open_id'  => $rsp['id'],
            'channel' => ConstCode::TYPE_FACEBOOK,
            'nickname'    => $rsp['name'],
            'gender'  => $this->getGender($rsp), //不一定会返回
            'avatar'  => $this->getAvatar($rsp),
        ];
        return $userinfo;
    }

    /**
     * 获取原始接口返回的用户信息
     */
    public function getUserInfo()
    {
        if($this->is_app === true){//App登录
            if(!isset($_REQUEST['access_token']) ){
                throw new \Exception("Facebook APP登录 需要传输access_token参数! ");
            }
            $this->token['access_token'] = $_REQUEST['access_token'];
        }else {
            $this->getToken();
            $fields = isset($this->config['fields']) ? $this->config['fields'] : 'id,name,gender,picture.width(400)';
            return $this->call('me', ['access_token' => $this->token['access_token'], 'fields' => $fields], 'GET');
        }
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
            'code'          => isset($_GET['code']) ? $_GET['code'] : '',
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
     * 格式化性别
     *
     * @param array $rsp
     * @return string
     */
    private function getGender($rsp)
    {
        $gender = isset($rsp['gender']) ? $rsp['gender'] : null;
        $return = ConstCode::GENDER;
        switch ($gender) {
            case 'male':
                $return = ConstCode::GENDER_MAN;
                break;
            case 'female':
                $return = ConstCode::GENDER_WOMEN;
                break;
        }
        return $return;
    }

    /**
     * 获取用户头像
     *
     * @param array $rsp
     * @return string
     */
    private function getAvatar($rsp)
    {
        if (isset($rsp['picture']['data']['url'])) {
            return $rsp['picture']['data']['url'];
        }
        return '';
    }
}
