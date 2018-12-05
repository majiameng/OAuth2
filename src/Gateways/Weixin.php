<?php

namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Helper\ConstCode;

class Weixin extends Gateway
{
    const API_BASE            = 'https://api.weixin.qq.com/sns/';
    protected $AuthorizeURL   = 'https://open.weixin.qq.com/connect/qrconnect';
    protected $AccessTokenURL = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    /**
     * Description:  得到跳转地址
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return string
     */
    public function getRedirectUrl()
    {
        $this->switchAccessTokenURL();
        $params = [
            'appid'         => $this->config['app_id'],
            'redirect_uri'  => $this->config['callback'],
            'response_type' => $this->config['response_type'],
            'scope'         => $this->config['scope'],
            'state'         => $this->config['state'],
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params) . '#wechat_redirect';
    }

    /**
     * Description:  获取中转代理地址
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return string
     */
    public function getProxyURL()
    {
        $params = [
            'appid'         => $this->config['app_id'],
            'response_type' => $this->config['response_type'],
            'scope'         => $this->config['scope'],
            'state'         => $this->config['state'],
            'return_uri'    => $this->config['callback'],
        ];
        return $this->config['proxy_url'] . '?' . http_build_query($params);
    }

    /**
     * Description:  获取当前授权用户的openid标识
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return mixed
     * @throws \Exception
     */
    public function openid()
    {
        $this->getToken();

        if (isset($this->token['openid'])) {
            return $this->token['openid'];
        } else {
            throw new \Exception('没有获取到微信用户ID！');
        }
    }

    /**
     * Description:  获取格式化后的用户信息
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return array
     */
    public function userInfo()
    {
        $rsp = $this->userinfoRaw();

        $userinfo = [
            'open_id' => $this->openid(),
            'union_id'=> isset($this->token['unionid']) ? $this->token['unionid'] : '',
            'channel' => ConstCode::TYPE_WECHAT,
            'nickname'=> $rsp['nickname'],
            'gender'  => $rsp['sex'],
            'avatar'  => $rsp['headimgurl'],
        ];
        return $userinfo;
    }

    /**
     * Description:  获取原始接口返回的用户信息
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return array
     */
    public function userInfoRaw()
    {
        $this->getToken();

        return $this->call('userinfo');
    }

    /**
     * Description:  发起请求
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $api
     * @param array $params
     * @param string $method
     * @return mixed
     */
    private function call($api, $params = [], $method = 'GET')
    {
        $method = strtoupper($method);

        $params['access_token'] = $this->token['access_token'];
        $params['openid']       = $this->openid();
        $params['lang']         = 'zh_CN';

        $data = $this->$method(self::API_BASE . $api, $params);
        return json_decode($data, true);
    }

    /**
     * Description:  根据第三方授权页面样式切换跳转地址
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    private function switchAccessTokenURL()
    {
        if ($this->display == 'mobile') {
            $this->AuthorizeURL = 'https://open.weixin.qq.com/connect/oauth2/authorize';
        } else {
            //微信扫码网页登录，只支持此scope
            $this->config['scope'] = 'snsapi_login';
        }
    }

    /**
     * Description:  默认的AccessToken请求参数
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return array
     */
    protected function accessTokenParams()
    {
        $params = [
            'appid'      => $this->config['app_id'],
            'secret'     => $this->config['app_secret'],
            'grant_type' => $this->config['grant_type'],
            'code'       => isset($_GET['code']) ? $_GET['code'] : '',
        ];
        return $params;
    }

    /**
     * Description:  解析access_token方法请求后的返回值
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param string $token 获取access_token的方法的返回值
     * @return mixed
     * @throws \Exception
     */
    protected function parseToken($token)
    {
        $data = json_decode($token, true);
        if (isset($data['access_token'])) {
            return $data;
        } else {
            throw new \Exception("获取微信 ACCESS_TOKEN 出错：{$token}");
        }
    }

}
