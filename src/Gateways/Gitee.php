<?php
/**
 * Gitee
 * api接口文档
 */
namespace ZhuoSheGuaMo\YxOAuth\Gateways;

use ZhuoSheGuaMo\YxOAuth\Connector\Gateway;
use ZhuoSheGuaMo\YxOAuth\Plug\ConstCode;

/**
 * Class Gitee
 */
class Gitee extends Gateway
{
    protected $AuthorizeURL   = 'https://gitee.com/oauth/authorize';
    protected $AccessTokenURL = 'https://gitee.com/oauth/token/';
    protected $UserInfoURL = 'https://gitee.com/api/v5/user';

    /**
     * Description:  得到跳转地址
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
     * @throws \Exception
     */
    public function userInfo()
    {
        $result = $this->getUserInfo();
        $userInfo = [
            'open_id' => isset($result['id']) ? $result['id'] : '',
            'union_id'=> isset($result['login']) ? $result['login'] : '',
            'channel' => ConstCode::TYPE_GITEE,
            'nickname'=> $result['name'],
            'gender'  => ConstCode::GENDER,
            'avatar'  => $result['avatar_url'],
            'birthday'=> '',
            'access_token'=> $this->token['access_token'] ?? '',
            'native'=> $result,
        ];
        return $userInfo;
    }

    /**
     * Description:  获取原始接口返回的用户信息
     * @return array
     * @throws \Exception
     */
    public function getUserInfo()
    {
        /** 获取用户信息 */
        $this->openid();

//        $headers = ['Authorization: Bearer '.$this->token['access_token']];
        $params = [
            'access_token'=>$this->token['access_token'],
        ];
        $data = $this->get($this->UserInfoURL,$params);
        return json_decode($data, true);
    }

    /**
     * Description:  获取当前授权用户的openid标识
     * @return mixed
     * @throws \Exception
     */
    public function openid()
    {
        $this->getToken();
    }


    /**
     * Description:  获取AccessToken
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
            throw new \Exception("获取Gitee ACCESS_TOKEN出错：{$data['error']}");
        }
    }

}