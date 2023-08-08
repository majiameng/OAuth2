<?php
/**
 * 蚂蚁金服 https://openhome.alipay.com
 * 支付宝第三方应用授权文档
 *    https://docs.open.alipay.com/20160728150111277227
 * 1.设置:回调地址/加签方式(RSA(SHA256)密钥)/接口内容加密方式(AES密钥)
 *     应用公钥(SHA256withRsa)生成方法
 *     1.1 在liunx 使用 openssl
 *     1.2 OpenSSL> genrsa -out rsa_private_key.pem   2048  #生成私钥
 *     1.3 OpenSSL> pkcs8 -topk8 -inform PEM -in rsa_private_key.pem -outform PEM -nocrypt -out rsa_private_key_pkcs8.pem #Java开发者需要将私钥转换成PKCS8格式
 *     1.4 OpenSSL> rsa -in rsa_private_key.pem -pubout -out rsa_public_key.pem #生成公钥
 *     1.5 OpenSSL> exit #退出OpenSSL程序
 *     1.6 复制rsa_public_key.pem 中间那一段到支付宝填写(注: 不要复制头和尾)
 * 2.1.PC登录需要签约:	第三方应用授权/获取会员信息
 * 2.2.APP登录需要签约:	APP支付宝登录/获取会员信息
 *
 * 网页授权文档：https://opendocs.alipay.com/open/284/web
 */
namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Helper\ConstCode;
use tinymeng\OAuth2\Helper\Str;

/**
 * Class Alipay
 * @package tinymeng\OAuth2\Gateways
 * @Author: TinyMeng <666@majiameng.com>
 * @Created: 2018/11/9
 */
class Alipay extends Gateway
{
    const RSA_PRIVATE = 1;
    const RSA_PUBLIC  = 2;

    const API_BASE            = 'https://openapi.alipay.com/gateway.do';
    protected $AuthorizeURL   = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm';

    /**
     * 非必须参数。接口权限值，目前只支持 auth_user 和 auth_base 两个值。以空格分隔的权限列表，若不传递此参数，代表请求的数据访问操作权限与上次获取Access Token时一致。通过Refresh Token刷新Access Token时所要求的scope权限范围必须小于等于上次获取Access Token时授予的权限范围。
     * @var string
     */
    protected $scope;

    /**
     * 商户生成签名字符串所使用的签名算法类型，目前支持RSA2和RSA，推荐使用RSA2.
     * @var string
     */
    protected $sign_type = 'RSA2';

    /**
     * @param $config
     * @throws \Exception
     */
    public function __construct($config)
    {
        parent::__construct($config);
        $this->AccessTokenURL = static::API_BASE;
    }

    /**
     * Description:  得到跳转地址
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return string
     */
    public function getRedirectUrl()
    {
        //存储state
        $this->saveState();
        //登录参数
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
        $result = $this->getUserInfo();

        $userInfo = [
            'open_id'  => $this->openid(),
            'union_id'  => $this->token['user_id'],
            'access_token'=> $this->token['access_token'] ?? '',
            'channel' => ConstCode::TYPE_ALIPAY,
            'nickname'    => $result['nick_name'],
            'gender'  => isset($result['gender']) ? $this->getGender($result['gender']) : ConstCode::GENDER,
            'avatar'  => $result['avatar'],
        ];
        $userInfo['type'] = ConstCode::getTypeConst($userInfo['channel'],$this->type);
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
        if($this->type == 'app'){//App登录
            if(!isset($_REQUEST['access_token']) ){
                throw new \Exception("AliPay APP登录 需要传输access_token参数! ");
            }
            $this->token['access_token'] = $_REQUEST['access_token'];
        }else {
            /** 获取token信息 */
            $this->getToken();
        }

        $params = [
            'app_id'     => $this->config['app_id'],
            'method'     => 'alipay.user.info.share',
            'charset'    => 'UTF-8',
            'sign_type'  => $this->sign_type,
            'timestamp'  => date("Y-m-d H:i:s"),
            'version'    => '1.0',
            'auth_token' => $this->token['access_token'],
        ];
        $params['sign'] = $this->signature($params);

        $data = $this->post(self::API_BASE, $params);
        $data = mb_convert_encoding($data, 'utf-8', 'gbk');
        $result =  json_decode($data, true);
        return $result['alipay_user_info_share_response'];
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
            'sign_type'  => $this->sign_type,
            'timestamp'  => date("Y-m-d H:i:s"),
            'version'    => '1.0',
            'grant_type' => $this->config['grant_type'],
            'code'       => $this->getCode(),
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
