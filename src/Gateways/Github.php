<?php
/**
 * Github  https://github.com/settings/developers
 * api接口文档
 *      https://developer.github.com/apps/building-oauth-apps/authorizing-oauth-apps/
 */
namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Helper\ConstCode;

class Github extends Gateway
{

    const API_BASE            = 'https://api.github.com/';
    protected $AuthorizeURL   = 'https://github.com/login/oauth/authorize';
    protected $AccessTokenURL = 'https://github.com/login/oauth/access_token';

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
            'client_id'    => $this->config['app_id'],
            'redirect_uri' => $this->config['callback'],
            'scope'        => $this->config['scope'],
            'state'        => $this->config['state'],
            'display'      => $this->display,
            'allow_signup' => true,//是否会在OAuth流程中为未经身份验证的用户提供注册GitHub的选项。默认是true。false在策略禁止注册的情况下使用。
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
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
            throw new \Exception('没有获取到新浪微博用户ID！');
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
        $rsp = $this->getUserInfo();

        $userInfo = [
            'open_id'  => $this->token['access_token'],
            'union_id'  => $rsp['id'],
            'channel' => ConstCode::TYPE_GITHUB,
            'nickname'    => $rsp['name'],
            'username'  => $rsp['login'],
            'avatar'  => $rsp['avatar_url'],
            'email'  => $rsp['email'],
            'sign'  => $rsp['bio'],
            'gender'  => 0,
        ];
        return $userInfo;
    }

    /**
     * Description:  获取原始接口返回的用户信息
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return mixed
     * @throws \Exception
     */
    public function getUserInfo()
    {
        $this->getToken();

        $url = self::API_BASE . "user";
        $headers = [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36 ',
            'Authorization: token ' . $this->token['access_token'],
            'Accept: application/json'
        ];
        $response = $this->get($url, [], $headers);
        $response = json_decode($response, true);
        if (!empty($data['error'])) {
            throw new \Exception($response);
        }
        return $response;
    }


    /**
     * Description:  根据第三方授权页面样式切换跳转地址
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    private function switchAccessTokenURL()
    {
        if ($this->display == 'mobile') {
            $this->AuthorizeURL = 'https://open.weibo.cn/oauth2/authorize';
        }
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
            throw new \Exception("获取GitHub ACCESS_TOKEN出错：{$data['error']}");
        }
    }
}
