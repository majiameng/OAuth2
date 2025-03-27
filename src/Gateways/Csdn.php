<?php
/**
 * CSDN
 * api接口文档
*/
namespace tinymeng\OAuth2\Gateways;
use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Helper\ConstCode;

/**
 * Class Csdn
 * @package tinymeng\OAuth2\Gateways
 * @Author: TinyMeng <666@majiameng.com>
 * @Created: 2023/07/09
 */
class Csdn extends Gateway
{
    const API_BASE = 'https://api.csdn.net/';
    protected $AuthorizeURL = 'https://passport.csdn.net/v2/oauth2/authorize';
    protected $AccessTokenURL = 'https://passport.csdn.net/v2/oauth2/token';
    protected $UserInfoURL = 'v1/user/getinfo';

    public function userInfo()
    {
        $result = $this->getUserInfo();
        return [
            'open_id'  => $result['username'] ?? '',
            'union_id' => $result['username'] ?? '',
            'channel' => ConstCode::TYPE_CSDN,
            'nickname' => $result['username'] ?? '',
            'gender'   => ConstCode::GENDER,
            'avatar'   => $result['avatar'] ?? '',
            'email'    => $result['email'] ?? '',
            'access_token' => $this->token['access_token'] ?? '',
            'native'   => $result
        ];
    }

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
     * Description:  得到跳转地址
     * @return string
     */
    public function getRedirectUrl()
    {
        $this->saveState();
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
     * Description:  获取原始接口返回的用户信息
     * @return array
     * @throws \Exception
     */
    public function getUserInfo()
    {
        $this->openid();
        $data = $this->get(static::API_BASE . $this->UserInfoURL, [
            'access_token' => $this->token['access_token']
        ]);
        $data = json_decode($data, true);
        
        if(!isset($data['username'])) {
            throw new \Exception("获取CSDN用户信息失败：" . ($data['error_description'] ?? '未知错误'));
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
        return $this->token['username'] ?? '';
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
        throw new \Exception("获取CSDN ACCESS_TOKEN出错：" . ($data['error_description'] ?? '未知错误'));
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
