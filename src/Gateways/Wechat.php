<?php
/**
 * 网站应用微信登录开发 https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419316505&token=
 * 1.PC登录:微信开放平台创建'网站应用'
 * 2.Mobile登录:微信公众号(服务号/企业号)
 * 3.APP登录:微信开放平台创建'移动应用'
 *
 * 注: scope值
 *      1、以snsapi_base为scope发起的网页授权，是用来获取进入页面的用户的openid的，并且是静默授权并自动跳转到回调页的。用户感知的就是直接进入了回调页（往往是业务页面）
 *      2、以snsapi_userinfo为scope发起的网页授权，是用来获取用户的基本信息的。但这种授权需要用户手动同意，并且由于用户同意过，所以无须关注，就可在授权后获取该用户的基本信息。(H5页面微信授权获取用户,注册成为用户id,可以做点赞关注等功能)
 *      3、用户管理类接口中的“获取用户基本信息接口”，是在用户和公众号产生消息交互或关注后事件推送后，才能根据用户OpenID来获取用户基本信息。这个接口，包括其他微信接口，都是需要该用户（即openid）关注了公众号后，才能调用成功的。
 * 如想打通unionid的话需要将公众号绑定到同一个微信开放平台
 */
namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Exception\OAuthException;
use tinymeng\OAuth2\Helper\ConstCode;

/**
 * Class Wechat
 * @package tinymeng\OAuth2\Gateways
 * @Author: TinyMeng <666@majiameng.com>
 * @Created: 2018/11/9
 */
class Wechat extends Gateway
{
    const API_BASE            = 'https://api.weixin.qq.com/sns/';
    protected $AuthorizeURL   = 'https://open.weixin.qq.com/connect/qrconnect';
    protected $AccessTokenURL = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    protected $jsCode2Session = 'https://api.weixin.qq.com/sns/jscode2session';

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

        //获取代理链接
        if(isset($this->config['proxy_url'])){
            return $this->getProxyURL();
        }

        //登录参数
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
            'redirect_uri'    => $this->config['callback'],
        ];
        return $this->config['proxy_url'] . '?' . http_build_query($params);
    }

    /**
     * Description:  获取当前授权用户的openid标识
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return mixed
     * @throws OAuthException
     */
    public function openid()
    {
        $this->getToken();

        if (isset($this->token['openid'])) {
            return $this->token['openid'];
        } else {
            throw new OAuthException('没有获取到微信用户ID！');
        }
    }

    /**
     * Description:  获取格式化后的用户信息
     * @return array
     * @throws OAuthException
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    public function userInfo()
    {
        $result = $this->getUserInfo();

        $userInfo = [
            'open_id' => $this->openid(),
            'union_id'=> $this->token['unionid'] ?? '',
            'access_token'=> $this->token['access_token'] ?? '',
            'channel' => ConstCode::TYPE_WECHAT,
            'nickname'=> $result['nickname']??'',
            'gender'  => $result['sex'] ?? ConstCode::GENDER,
            'avatar'  => $result['headimgurl']??'',
            'type'    => ConstCode::getTypeConst(ConstCode::TYPE_WECHAT, $this->type),
            // 额外信息
            'session_key'  => $result['session_key']??'',
            'native'   => $result,
        ];
        return $userInfo;
    }

    /**
     * Description:  获取原始接口返回的用户信息
     * @return array
     * @throws OAuthException
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    public function getUserInfo()
    {
        if($this->type == 'app'){//App登录
            if(!isset($_REQUEST['access_token']) ){
                throw new OAuthException("Wechat APP登录 需要传输access_token参数! ");
            }
            $this->token['access_token'] = $_REQUEST['access_token'];
        }elseif ($this->type == 'applets'){
            //小程序
            return $this->applets();
        }else {
            /** 获取token信息 */
            $this->getToken();
        }

        /** 获取用户信息 */
        $params = [
            'access_token'=>$this->token['access_token'],
            'openid'=>$this->openid(),
            'lang'=>'zh_CN',
        ];
        $data = $this->get(self::API_BASE . 'userinfo', $params);
        return json_decode($data, true);
    }

    /**
     * @return array|mixed|null
     * @throws OAuthException
     */
    public function applets(){
        /** 获取参数 */
        $params = $this->accessTokenParams();
        $params['js_code'] = $params['code'];

        /** 获取access_token */
        $token =  $this->get($this->jsCode2Session, $params);
        /** 解析token值(子类实现此方法) */
        $this->token = $this->parseToken($token);
        return $this->token;
    }

    /**
     * Description:  根据第三方授权页面样式切换跳转地址
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    private function switchAccessTokenURL()
    {
        /**
         *  第三方使用网站应用授权登录前请注意已获取相应网页授权作用域
         *  Pc网站应用 https://open.weixin.qq.com/connect/qrconnect?appid=APPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect
         *  微信内网站应用: https://open.weixin.qq.com/connect/oauth2/authorize?appid=APPID&redirect_uri=REDIRECT_URL&response_type=code&scope=SCOPE&state=1#wechat_redirect
         */
        if ($this->display == 'mobile') {
            $this->AuthorizeURL = 'https://open.weixin.qq.com/connect/oauth2/authorize';
        } else {
            //微信扫码网页登录，只支持此scope
            $this->config['scope'] = 'snsapi_login';
        }
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
            'appid'      => $this->config['app_id'],
            'secret'     => $this->config['app_secret'],
            'grant_type' => $this->config['grant_type'],
            'code'       => $this->getCode(),
        ];
        return $params;
    }

    /**
     * Description:  解析access_token方法请求后的返回值
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param string $token 获取access_token的方法的返回值
     * @return mixed
     * @throws OAuthException
     */
    protected function parseToken($token)
    {
        $data = json_decode($token, true);
        if (isset($data['access_token'])) {
            return $data;
        }elseif (isset($data['session_key'])){
            //小程序登录
            return $data;
        } else {
            throw new OAuthException("获取微信 ACCESS_TOKEN 出错：{$token}");
        }
    }

    /**
     * 解密小程序 wx.getUserInfo() 敏感数据.
     * @param string $encryptedData
     * @param string $iv
     * @param string $sessionKey
     * @return array
     */
    public function descryptData($encryptedData, $iv, $sessionKey)
    {
        if (24 != strlen($sessionKey))
        {
            throw new \InvalidArgumentException('sessionKey 格式错误');
        }
        if (24 != strlen($iv))
        {
            throw new \InvalidArgumentException('iv 格式错误');
        }
        $aesKey = base64_decode($sessionKey);
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result = openssl_decrypt($aesCipher, 'AES-128-CBC', $aesKey, 1, $aesIV);
        if (!$result)
        {
            throw new \InvalidArgumentException('解密失败');
        }
        $dataObj = json_decode($result, true);
        if (!$dataObj)
        {
            throw new \InvalidArgumentException('反序列化数据失败');
        }

        return $dataObj;
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
            'appid'         => $this->config['app_id'],
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
        ];
        
        $token = $this->get('https://api.weixin.qq.com/sns/oauth2/refresh_token', $params);
        $token = $this->parseToken($token);
        
        if (isset($token['access_token'])) {
            $this->token = $token;
            return true;
        }
        return false;
    }

    /**
     * 检验授权凭证AccessToken是否有效
     * @param string $accessToken
     * @return bool
     */
    public function validateAccessToken($accessToken = null)
    {
        try {
            $accessToken = $accessToken ?? $this->token['access_token'];
            $params = [
                'access_token' => $accessToken,
                'openid'      => $this->openid(),
            ];
            $result = $this->get(self::API_BASE . 'auth', $params);
            $result = json_decode($result, true);
            return isset($result['errcode']) && $result['errcode'] == 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}