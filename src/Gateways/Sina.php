<?php

namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Helper\ConstCode;

class Sina extends Gateway
{

    const API_BASE            = 'https://api.weibo.com/2/';
    protected $AuthorizeURL   = 'https://api.weibo.com/oauth2/authorize';
    protected $AccessTokenURL = 'https://api.weibo.com/oauth2/access_token';

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

        $userinfo = [
            'open_id'  => $this->token['access_token'],
            'union_id'  => $this->openid(),
            'channel' => ConstCode::TYPE_SINA,
            'nickname'    => $rsp['screen_name'],
            'gender'  => $rsp['gender'] == 'm' ? ConstCode::GENDER_MAN : ConstCode::GENDER_WOMEN,
            'avatar'  => $rsp['avatar_hd'],
        ];
        return $userinfo;
    }

    /**
     * Description:  获取原始接口返回的用户信息
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return array
     */
    public function getUserInfo()
    {
        $this->getToken();

        return $this->call('users/show.json', ['uid' => $this->openid()]);
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
            $data['openid'] = $data['uid'];
            unset($data['uid']);
            return $data;
        } else {
            throw new \Exception("获取新浪微博ACCESS_TOKEN出错：{$data['error']}");
        }
    }
}
