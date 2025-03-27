<?php
/**
 * Apple Sign In https://developer.apple.com/sign-in-with-apple/
 */
namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Exception\OAuthException;
use tinymeng\OAuth2\Helper\ConstCode;

class Apple extends Gateway
{
    const API_BASE = 'https://appleid.apple.com/';
    protected $AuthorizeURL = 'https://appleid.apple.com/auth/authorize';
    protected $AccessTokenURL = 'https://appleid.apple.com/auth/token';

    /**
     * 得到跳转地址
     */
    public function getRedirectUrl()
    {
        $this->saveState();
        $params = [
            'client_id'     => $this->config['app_id'],
            'redirect_uri'  => $this->config['callback'],
            'response_type' => $this->config['response_type'],
            'scope'         => $this->config['scope'] ?: 'name email',
            'response_mode' => 'form_post',
            'state'         => $this->config['state'],
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
    }

    /**
     * 获取当前授权用户的openid标识
     */
    public function openid()
    {
        $this->getToken();
        return $this->token['sub'] ?? '';
    }

    /**
     * 获取格式化后的用户信息
     */
    public function userInfo()
    {
        $userInfo = [
            'open_id'      => $this->openid(),
            'union_id'     => $this->token['sub'] ?? '',
            'channel'      => ConstCode::TYPE_APPLE,
            'nickname'     => $this->token['email'] ?? '',
            'gender'       => ConstCode::GENDER,
            'avatar'       => '',
            'access_token' => $this->token['access_token'] ?? '',
            'native'       => $this->token
        ];
        return $userInfo;
    }

    /**
     * 获取access_token
     */
    protected function getToken()
    {
        if (empty($this->token)) {
            $this->checkState();
            
            // 生成客户端密钥
            $clientSecret = $this->generateClientSecret();
            
            $params = [
                'client_id'     => $this->config['app_id'],
                'client_secret' => $clientSecret,
                'code'          => isset($_REQUEST['code']) ? $_REQUEST['code'] : '',
                'grant_type'    => $this->config['grant_type'],
                'redirect_uri'  => $this->config['callback'],
            ];
            
            $response = $this->post($this->AccessTokenURL, $params);
            $this->token = $this->parseToken($response);
            
            // 解析 ID Token
            $this->token = array_merge($this->token, $this->parseIdToken($this->token['id_token']));
        }
    }

    /**
     * 生成客户端密钥
     */
    protected function generateClientSecret()
    {
        $key = openssl_pkey_get_private($this->config['private_key']);
        if (!$key) {
            throw new OAuthException('私钥无效');
        }

        $headers = [
            'kid' => $this->config['key_id'],
            'alg' => 'ES256'
        ];

        $claims = [
            'iss' => $this->config['team_id'],
            'iat' => time(),
            'exp' => time() + 86400 * 180,
            'aud' => 'https://appleid.apple.com',
            'sub' => $this->config['app_id'],
        ];

        $payload = $this->base64UrlEncode(json_encode($headers)) . '.' . 
                  $this->base64UrlEncode(json_encode($claims));

        openssl_sign($payload, $signature, $key, OPENSSL_ALGO_SHA256);
        
        return $payload . '.' . $this->base64UrlEncode($signature);
    }

    /**
     * Base64Url 编码
     */
    protected function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * 解析 ID Token
     */
    protected function parseIdToken($idToken)
    {
        $tokens = explode('.', $idToken);
        if (count($tokens) != 3) {
            throw new OAuthException('无效的 ID Token');
        }
        
        $payload = json_decode(base64_decode($tokens[1]), true);
        if (!$payload) {
            throw new OAuthException('无效的 Token Payload');
        }
        
        return $payload;
    }

    /**
     * 解析access_token方法请求后的返回值
     */
    protected function parseToken($token)
    {
        $data = json_decode($token, true);
        if (isset($data['access_token'])) {
            return $data;
        }
        throw new OAuthException('获取Apple access_token 出错：' . json_encode($data));
    }
}