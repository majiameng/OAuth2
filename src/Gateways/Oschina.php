<?php
/**
 * Oschina
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
class Oschina extends Gateway
{
    const API_BASE = 'https://www.oschina.net/';
    protected $AuthorizeURL = 'https://www.oschina.net/action/oauth2/authorize';
    protected $AccessTokenURL = 'https://www.oschina.net/action/openapi/token';
    protected $UserInfoURL = 'action/openapi/user';

    public function userInfo()
    {
        $result = $this->getUserInfo();
        $userInfo = [
            'open_id'  => $result['id'] ?? '',
            'union_id' => $result['id'] ?? '',
            'access_token' => $this->token['access_token'] ?? '',
            'channel' => ConstCode::TYPE_OSCHINA,
            'nickname' => $result['name'] ?? '',
            'gender'   => isset($result['gender']) ? ($result['gender'] == 'male' ? 1 : 2) : ConstCode::GENDER,
            'avatar'   => $result['avatar'] ?? '',
            // 以下是额外信息
            'email'    => $result['email'] ?? '',
            'native'   => $result
        ];
        return $userInfo;
    }

    public function getUserInfo()
    {
        $this->openid();
        $data = $this->get($this->UserInfoURL, [
            'access_token' => $this->token['access_token'],
            'dataType' => 'json'
        ]);
        $data = json_decode($data, true);
        
        if(!isset($data['id'])) {
            throw new OAuthException("获取OSChina用户信息失败：" . ($data['error_description'] ?? '未知错误'));
        }
        return $data;
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
                'dataType'      => 'json'
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
        throw new OAuthException("获取OSChina ACCESS_TOKEN出错：" . ($data['error_description'] ?? '未知错误'));
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
            'dataType'      => 'json'
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
