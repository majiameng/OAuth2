<?php
/**
 * Gitlab
 * api接口文档
*/
namespace tinymeng\OAuth2\Gateways;
use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Exception\OAuthException;
use tinymeng\OAuth2\Helper\ConstCode;

/**
 * Class Gitlab
 * @package tinymeng\OAuth2\Gateways
 * @Author: TinyMeng <666@majiameng.com>
 * @Created: 2023/07/09
 */
class Gitlab extends Gateway
{
    const API_BASE = 'https://gitlab.com/';
    protected $AuthorizeURL = 'https://gitlab.com/oauth/authorize';
    protected $AccessTokenURL = 'https://gitlab.com/oauth/token';
    protected $UserInfoURL = 'api/v4/user';

    public function getRedirectUrl()
    {
        $this->saveState();
        $params = [
            'client_id'     => $this->config['app_id'],
            'redirect_uri'  => $this->config['callback'],
            'response_type' => $this->config['response_type'],
            'state'         => $this->config['state'],
            'scope'         => $this->config['scope'] ?: 'read_user',
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
    }

    public function userInfo()
    {
        $result = $this->getUserInfo();
        return [
            'open_id'  => $result['id'] ?? '',
            'union_id' => $result['id'] ?? '',
            'channel' => ConstCode::TYPE_GITLAB,
            'nickname' => $result['username'] ?? '',
            'gender'   => ConstCode::GENDER,
            'avatar'   => $result['avatar_url'] ?? '',
            'email'    => $result['email'] ?? '',
            'access_token' => $this->token['access_token'] ?? '',
            'native'   => $result
        ];
    }

    public function getUserInfo()
    {
        $this->openid();
        $headers = ['Authorization: Bearer ' . $this->token['access_token']];
        $data = $this->get(static::API_BASE . $this->UserInfoURL, [], $headers);
        $data = json_decode($data, true);
        
        if(!isset($data['id'])) {
            throw new OAuthException("获取GitLab用户信息失败：" . ($data['error_description'] ?? '未知错误'));
        }
        return $data;
    }

    /**
     * Description:  获取当前授权用户的openid标识
     * @return string
     * @throws OAuthException
     */
    public function openid()
    {
        $this->getToken();
        return $this->token['user_id'] ?? '';
    }

    /**
     * Description:  获取AccessToken
     * @throws OAuthException
     */
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
     * Description:  解析access_token方法请求后的返回值
     * @param $token
     * @return mixed
     * @throws OAuthException
     */
    protected function parseToken($token)
    {
        $data = json_decode($token, true);
        if (isset($data['access_token'])) {
            return $data;
        }
        throw new OAuthException("获取GitLab ACCESS_TOKEN出错：" . ($data['error_description'] ?? '未知错误'));
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
        
        $token = $this->post($this->AccessTokenURL, $params);
        $token = $this->parseToken($token);
        
        if (isset($token['access_token'])) {
            $this->token = $token;
            return true;
        }
        return false;
    }
}
