<?php
/**
 * Google Api 控制台 https://console.developers.google.com
 * Oauth开发文档:
 *      https://developers.google.com/identity/protocols/OAuth2?csw=1
 *      https://developers.google.com/identity/protocols/OAuth2WebServer
 * 1.创建项目->并创建凭证
 */
namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Helper\ConstCode;

class Google extends Gateway
{
    const API_BASE            = 'https://www.googleapis.com/';
    const AUTHORIZE_URL       = 'https://accounts.google.com/o/oauth2/v2/auth';
    protected $AccessTokenURL = 'https://www.googleapis.com/oauth2/v4/token';

    /**
     * 得到跳转地址
     */
    public function getRedirectUrl()
    {
        $params = [
            'client_id'     => $this->config['app_id'],
            'redirect_uri'  => $this->config['callback'],
            'response_type' => $this->config['response_type'],
            'scope'         => $this->config['scope'],
            'state'         => $this->config['state'],
        ];
        return self::AUTHORIZE_URL . '?' . http_build_query($params);
    }

    /**
     * 获取当前授权用户的openid标识
     */
    public function openid()
    {
        $userinfo = $this->getUserInfo();
        return $userinfo['id'];
    }

    /**
     * 获取格式化后的用户信息
     */
    public function userInfo()
    {
        $result = $this->getUserInfo();

        /** 格式化性别 */
        $gender = ConstCode::GENDER;
        if(!empty($result['gender'])){
            switch ($result['gender']) {
                case 'male':
                    $gender = ConstCode::GENDER_MAN;
                    break;
                case 'female':
                    $gender = ConstCode::GENDER_WOMEN;
                    break;
            }
        }

        $userInfo = [
            'open_id'  => $this->token['access_token'],
            'union_id'  => $result['id'],
            'channel' => ConstCode::TYPE_GOOGLE,
            'nickname'    => $result['name'],
            'gender'  => $gender,
            'avatar'  => $result['picture'],
        ];
        return $userInfo;
    }

    /**
     * 获取原始接口返回的用户信息
     */
    public function getUserInfo()
    {
        $this->getToken();

        $headers = ['Authorization : Bearer ' . $this->token['access_token']];
        $data = $this->get(self::API_BASE . 'oauth2/v2/userinfo', '', $headers);
        return json_decode($data, true);
    }

    /**
     * Description:  解析access_token方法请求后的返回值
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $token
     * @return mixed
     * @throws \Exception
     */
    protected function parseToken($token)
    {
        $data = json_decode($token, true);
        if (isset($data['access_token'])) {
            return $data;
        } else {
            throw new \Exception("获取谷歌 ACCESS_TOKEN 出错：{$token}");
        }
    }

}
