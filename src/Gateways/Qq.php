<?php

namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Helper\ConstCode;

class Qq extends Gateway
{
    const API_BASE            = 'https://graph.qq.com/';
    protected $AuthorizeURL   = 'https://graph.qq.com/oauth2.0/authorize';
    protected $AccessTokenURL = 'https://graph.qq.com/oauth2.0/token';

    /**
     * Description:  得到跳转地址
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return string
     */
    public function getRedirectUrl()
    {
        $params = [
            'response_type' => $this->config['response_type'],
            'client_id'     => $this->config['app_id'],
            'redirect_uri'  => $this->config['callback'],
            'state'         => $this->config['state'],
            'scope'         => $this->config['scope'],
            'display'       => $this->display,
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
    }

    /**
     * Description:  获取当前授权用户的openid标识
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return mixed
     */
    public function openid()
    {
        $this->getToken();

        if (!isset($this->token['openid']) || !$this->token['openid']) {
            $userID                 = $this->getOpenID();
            $this->token['openid']  = $userID['openid'];
            $this->token['unionid'] = isset($userID['unionid']) ? $userID['unionid'] : '';
        }

        return $this->token['openid'];
    }

    /**
     * Description:  获取格式化后的用户信息
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return array
     */
    public function userInfo()
    {
        $result = $this->userInfoRaw();

        $userInfo = [
            'open_id' => $this->openid(),
            'union_id'=> isset($this->token['unionid']) ? $this->token['unionid'] : '',
            'channel' => ConstCode::TYPE_QQ,
            'nickname'=> $result['nickname'],
            'gender'  => $result['gender'] == "男" ? ConstCode::GENDER_MAN  : ConstCode::GENDER_WOMEN,
            'avatar'  => $result['figureurl_qq_2'] ? $result['figureurl_qq_2'] : $result['figureurl_qq_1'],
            'birthday'=> date('Y-m-d',strtotime($result['year'])),
        ];
        return $userInfo;
    }

    /**
     * Description:  获取原始接口返回的用户信息
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return array
     */
    public function userInfoRaw()
    {
        return $this->call('user/get_user_info');
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

        $params['openid']             = $this->openid();
        $params['oauth_consumer_key'] = $this->config['app_id'];
        $params['access_token']       = $this->token['access_token'];
        $params['format']             = 'json';

        $data = $this->$method(self::API_BASE . $api, $params);

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
        parse_str($token, $data);
        if (isset($data['access_token'])) {
            return $data;
        } else {
            throw new \Exception("获取腾讯QQ ACCESS_TOKEN 出错：" . $token);
        }
    }

    /**
     * Description:  通过接口获取openid
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return mixed|string
     * @throws \Exception
     */
    private function getOpenID()
    {
        $client = new \GuzzleHttp\Client();

        $query = ['access_token' => $this->token['access_token']];
        /** 如果要获取unionid，先去申请：http://wiki.connect.qq.com/开发者反馈 */
        if (isset($this->config['is_unioid']) && $this->config['is_unioid'] === true) {
            $query['unionid'] = 1;
        }

        $response = $client->request('GET', self::API_BASE . 'oauth2.0/me', ['query' => $query]);
        $data     = $response->getBody()->getContents();
        $data     = json_decode(trim(substr($data, 9), " );\n"), true);
        if (isset($data['openid'])) {
            return $data;
        } else {
            throw new \Exception("获取用户openid出错：" . $data['error_description']);
        }
    }
}
