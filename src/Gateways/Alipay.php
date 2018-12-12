<?php
/**
 * 蚂蚁金服 https://openhome.alipay.com
 * 支付宝第三方应用授权文档
 *    https://docs.open.alipay.com/20160728150111277227
 * 1.设置:回调地址/加签方式(RSA(SHA256)密钥)/接口内容加密方式(AES密钥)
 * 2.1.PC登录需要签约:	第三方应用授权/获取会员信息
 * 2.2.APP登录需要签约:	APP支付宝登录/获取会员信息
 */
namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Helper\ConstCode;
use tinymeng\OAuth2\Helper\Str;

class Alipay extends Gateway
{
    const RSA_PRIVATE = 1;
    const RSA_PUBLIC  = 2;

    const API_BASE            = 'https://openapi.alipay.com/gateway.do';
    protected $AuthorizeURL   = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm';
    protected $AccessTokenURL = 'https://openapi.alipay.com/gateway.do';

    /**
     * Description:  得到跳转地址
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return string
     */
    public function getRedirectUrl()
    {
        $params = [
            'app_id'       => $this->config['app_id'],
            'redirect_uri' => $this->config['callback'],
            'scope'        => $this->config['scope'],
            'state'        => $this->config['state'],
        ];

        if ($this->config['is_sandbox'] == true) {
            //使用沙箱环境url
            $this->AuthorizeURL = str_replace("alipay", "alipaydev", $this->AuthorizeURL);
        }

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
            throw new \Exception('没有获取到支付宝用户ID！');
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
            'union_id'  => $this->token['openid'],
            'channel' => ConstCode::TYPE_ALIPAY,
            'nickname'    => $rsp['nick_name'],
            'gender'  => strtolower($rsp['gender']) == 'm' ? ConstCode::GENDER_MAN : ConstCode::GENDER_WOMEN,
            'avatar'  => $rsp['avatar'],
        ];
        return $userinfo;
    }

    /**
     * Description:  获取原始接口返回的用户信息
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return mixed
     */
    public function getUserInfo()
    {
        $this->getToken();

        $params = [
            'app_id'     => $this->config['app_id'],
            'method'     => 'alipay.user.info.share',
            'charset'    => 'UTF-8',
            'sign_type'  => 'RSA2',
            'timestamp'  => date("Y-m-d H:i:s"),
            'version'    => '1.0',
            'auth_token' => $this->token['access_token'],
        ];
        $params['sign'] = $this->signature($params);

        $data = $this->post(self::API_BASE, $params);
        $data = mb_convert_encoding($data, 'utf-8', 'gbk');
        $rsp =  json_decode($data, true);
        return $rsp['alipay_user_info_share_response'];
    }

    /**
     * Description:  重写 获取的AccessToken请求参数
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return array
     */
    protected function accessTokenParams()
    {
        $params = [
            'app_id'     => $this->config['app_id'],
            'method'     => 'alipay.system.oauth.token',
            'charset'    => 'UTF-8',
            'sign_type'  => 'RSA2',
            'timestamp'  => date("Y-m-d H:i:s"),
            'version'    => '1.0',
            'grant_type' => $this->config['grant_type'],
            'code'       => isset($_GET['auth_code']) ? $_GET['auth_code'] : '',
        ];
        $params['sign'] = $this->signature($params);
        return $params;
    }

    /**
     * Description:  支付宝签名
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param array $data
     * @return string
     * @throws \Exception
     */
    private function signature($data = [])
    {
        ksort($data);
        $str = Str::buildParams($data);

        $rsaKey = $this->getRsaKeyVal(self::RSA_PRIVATE);
        $res    = openssl_get_privatekey($rsaKey);
        if ($res !== false) {
            $sign = '';
            openssl_sign($str, $sign, $res, OPENSSL_ALGO_SHA256);
            openssl_free_key($res);
            return base64_encode($sign);
        }
        throw new \Exception('支付宝RSA私钥不正确');
    }

    /**
     * Description:  获取密钥
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param int $type
     * @return string
     * @throws \Exception
     */
    private function getRsaKeyVal($type = self::RSA_PUBLIC)
    {
        if ($type === self::RSA_PUBLIC) {
            $keyname = 'pem_public';
            $header  = '-----BEGIN PUBLIC KEY-----';
            $footer  = '-----END PUBLIC KEY-----';
        } else {
            $keyname = 'pem_private';
            $header  = '-----BEGIN RSA PRIVATE KEY-----';
            $footer  = '-----END RSA PRIVATE KEY-----';
        }
        $rsa = $this->config[$keyname];
        if (is_file($rsa)) {
            $rsa = file_get_contents($rsa);
        }
        if (empty($rsa)) {
            throw new \Exception('支付宝RSA密钥未配置');
        }
        $rsa    = str_replace([PHP_EOL, $header, $footer], '', $rsa);
        $rsaVal = $header . PHP_EOL . chunk_split($rsa, 64, PHP_EOL) . $footer;
        return $rsaVal;
    }

    /**
     * Description:  解析access_token方法
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $token
     * @return mixed
     * @throws \Exception
     */
    protected function parseToken($token)
    {
        $token = mb_convert_encoding($token, 'utf-8', 'gbk');
        $data  = json_decode($token, true);

        if (isset($data['alipay_system_oauth_token_response'])) {
            $data           = $data['alipay_system_oauth_token_response'];
            $data['openid'] = $data['user_id'];
            return $data;
        } else {
            throw new \Exception("获取支付宝 ACCESS_TOKEN 出错：{$token}");
        }
    }
}
