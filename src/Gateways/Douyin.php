<?php
/**
 * 网站应用抖音登录开发 https://developer.open-douyin.com/docs/resource/zh-CN/dop/develop/openapi/account-permission/douyin-get-permission-code
 * 抖音小程序开发 https://developer.open-douyin.com/docs/resource/zh-CN/mini-game/develop/server/log-in/code-2-session/
 * 注: scope值
 *      1、以 trial.whitelist 放平台里申请了白名单用户一直显示待绑定，请查看https://developer.open-douyin.com/docs/resource/zh-CN/dop/common-question/faq/
 *      2、以 user_info为scope发起的网页授权，是用来获取用户的基本信息的。
 *      3、以 login_id为scope静默授权。
 * 如想打通unionid的话需要将小程序绑定到同一个抖音开放平台
 */
namespace tinymeng\OAuth2\Gateways;

use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Exception\OAuthException;
use tinymeng\OAuth2\Helper\ConstCode;

/**
 * Class Douyin
 * @package tinymeng\OAuth2\Gateways
 * @Author: TinyMeng <666@majiameng.com>
 * @Created: 2018/11/9
 */
class Douyin extends Gateway
{
    protected $ApiBase            = 'https://open.douyin.com';
    protected $AuthorizeURL   = 'https://open.douyin.com/platform/oauth/connect/';
    protected $AuthorizeSilenceURL   = 'https://open.douyin.com/oauth/authorize/v2/';//抖音静默授权
    protected $AccessTokenURL = 'oauth/access_token/';
    protected $UserInfoURL = 'oauth/userinfo/';

    protected $jsCode2Session = 'https://minigame.zijieapi.com/mgplatform/api/apps/jscode2session';

    protected $API_BASE_ARRAY = [
        'douyin'=>'https://open.douyin.com/',//抖音
        'toutiao'=>'https://open.snssdk.com/',//头条
        'xigua'=>'https://open-api.ixigua.com/',//西瓜
    ];

    public $oauth_type = ConstCode::TYPE_DOUYIN;//抖音

    /**
     * @param $config
     * @throws OAuthException
     */
    public function __construct($config)
    {
        parent::__construct($config);
        //切换方式
        $this->switchAccessTokenURL();
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
            'client_key'    => $this->config['app_id'],
            'redirect_uri'  => $this->config['callback'],
            'response_type' => $this->config['response_type'],
            'scope'         => $this->config['scope'],
            'optionalScope' => $this->config['optionalScope']??'',
            'state'         => $this->config['state'],
        ];
        if($params['state'] == 'login_id'){
            /**
             * 抖音静默获取授权码
             * https://developer.open-douyin.com/docs/resource/zh-CN/dop/develop/openapi/account-permission/douyin-default-get-permission-code
             */
            return $this->AuthorizeSilenceURL . '?' . http_build_query($params);
        }else{
            return $this->AuthorizeURL . '?' . http_build_query($params);
        }
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

        if (isset($this->token['open_id'])) {
            return $this->token['open_id'];
        } else {
            throw new OAuthException('没有获取到抖音用户ID！');
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
        //登录参数
        $result = $this->getUserInfo();

        $userInfo = [
            'open_id' => $this->openid(),
            'access_token'=> $this->token['access_token'] ?? '',
            'union_id'=> $this->token['unionid'] ?? '',
            'channel' => $this->oauth_type,
            'nickname'=> $result['nickname']??'',
            'gender'  => $result['gender'] ?? ConstCode::GENDER,
            'type'  => ConstCode::getTypeConst($this->oauth_type,$this->type),
            'avatar'  => $result['avatar']??'',
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
                throw new OAuthException("Douyin APP登录 需要传输access_token参数! ");
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
            'open_id'=>$this->openid(),
        ];
        $data = $this->get($this->UserInfoURL, $params);

        return $this->parseUserInfo($data);
    }

    /**
     * @return array|mixed|null
     * @throws OAuthException
     */
    public function applets(){
        /** 获取参数 */
        $params = $this->jscode2sessionParams();

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
        switch ($this->oauth_type){
            case ConstCode::TYPE_DOUYIN:$this->ApiBase = $this->API_BASE_ARRAY['douyin'];break;
            case ConstCode::TYPE_TOUTIAO:$this->ApiBase = $this->API_BASE_ARRAY['toutiao'];break;
            case ConstCode::TYPE_XIGUA:$this->ApiBase = $this->API_BASE_ARRAY['xigua'];break;
            default:throw new OAuthException("获取抖音 OAUTH_TYPE 参数出错：{$this->oauth_type}");
        }
        $this->AccessTokenURL = $this->ApiBase.$this->AccessTokenURL;
        $this->UserInfoURL = $this->ApiBase.$this->UserInfoURL;
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
            'client_key'      => $this->config['app_id'],
            'client_secret'     => $this->config['app_secret'],
            'grant_type' => $this->config['grant_type'],
            'code'       => $this->getCode(),
        ];

        return $params;
    }

    /**
     * Description:  重写 获取的jscode2sessionParams请求参数
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return array
     */
    protected function jscode2sessionParams()
    {
        $params = [
            'appid'      => $this->config['app_id'],
            'secret'     => $this->config['app_secret'],
        ];
        if(isset($_REQUEST['code'])) $params['code'] = $_REQUEST['code'];
        if(isset($_REQUEST['anonymous_code'])) $params['anonymous_code'] = $_REQUEST['anonymous_code'];

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
        if (isset($data['data']['access_token'])) {
            return $data['data'];
        }elseif (isset($data['session_key'])){
            //小程序登录
            return $data;
        } else {
            throw new OAuthException("获取抖音 ACCESS_TOKEN 出错：{$token}");
        }
    }

    /**
     * Description:  解析access_token方法请求后的返回值
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param string $token 获取access_token的方法的返回值
     * @return mixed
     * @throws OAuthException
     */
    protected function parseUserInfo($data)
    {
        $data = json_decode($data, true);
        if (isset($data['message']) && $data['message'] == 'success') {
            return $data['data'];
        } else {
            throw new OAuthException("获取抖音 UserInfo 出错：{$data['data']['description']}");
        }
    }

}
