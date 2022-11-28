<?php
namespace tinymeng\OAuth2\Connector;

use tinymeng\OAuth2\Helper\ConstCode;
use tinymeng\OAuth2\Helper\Str;

/**
 * 所有第三方登录必须继承的抽象类
 */
abstract class Gateway implements GatewayInterface
{
    /**
     * 配置参数
     * @var array
     */
    protected $config;

    /**
     * AppId
     * @var array
     */
    protected $app_id;

    /**
     * AppSecret
     * @var string
     */
    protected $app_secret;

    /**
     * 回调地址
     * @var string
     */
    protected $callback;

    /**
     * 当前时间戳
     * @var int
     */
    protected $timestamp;

    /**
     * 默认第三方授权页面样式
     * @var string
     */
    protected $display = 'default';

    /**
     * 登录类型：app applets
     * @var bool
     */
    protected $type;

    /**
     * 第三方Token信息
     * @var array
     */
    protected $token = null;

    /**
     * 是否验证回跳地址中的state参数
     * @var boolean
     */
    protected $checkState = false;

    /**
     * 第三方返回的userInfo
     * @var array
     */
    protected $userInfo = [];

    /**
     * 格式化的userInfo
     * @var array
     */
    protected $formatUserInfo = [];

    /**
     * Gateway constructor.
     * @param $config
     * @throws \Exception
     */
    public function __construct($config)
    {
        if (!$config) {
            throw new \Exception('传入的配置不能为空');
        }
        if(isset($_GET['referer']) && $config['callback']){
            $config['callback'] .= ((strpos($config, '?') !== false) ? '&' : '?').'referer='.base64_encode($_GET['referer']);
        }
        //默认参数
        $_config = [
            'app_id'        => '',
            'app_secret'    => '',
            'callback'      => '',
            'response_type' => 'code',
            'grant_type'    => 'authorization_code',
            'proxy'         => '',
            'state'         => '',
            'type'          => '',
            'is_sandbox'    => false,//是否是沙箱环境
        ];
        $this->config    = array_merge($_config, $config);
        foreach($this->config as $key=>$val){
            if(property_exists($this,$key)) $this->$key=$val;
        }
        $this->timestamp = time();
    }

    /**
     * Description:  设置授权页面样式
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $display
     * @return $this
     */
    public function setDisplay($display)
    {
        $this->display = $display;
        return $this;
    }

    /**
     * Description:  设置是否是App
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Description:  强制验证回跳地址中的state参数
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return $this
     */
    public function mustCheckState(){
        $this->checkState = true;
        return $this;
    }

    /**
     * 获取配置信息
     * @Author: TinyMeng <666@majiameng.com>
     * @return array
     */
    public function getConfig(){
        return $this->config;
    }

    /**
     * 设置token(App登录时)
     * @param $token
     * @return $this
     */
    public function setToken($token){
        $this->token = $token;
        return $this;
    }

    /**
     * 存储state
     * @Author: TinyMeng <666@majiameng.com>
     */
    public function saveState(){
        if ($this->checkState === true) {
            //是否开启session
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            if(empty($this->config['state'])){
                $this->config['state'] = Str::random();//生成随机state
            }
            //存储到session
            $_SESSION['tinymeng_oauth_state'] = $this->config['state'];
        }
    }

    /**
     * 验证state
     * @Author: TinyMeng <666@majiameng.com>
     * @throws \Exception
     */
    public function CheckState(){
        if ($this->checkState === true) {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            if (!isset($_REQUEST['state']) || !isset($_SESSION['tinymeng_oauth_state']) || $_REQUEST['state'] != $_SESSION['tinymeng_oauth_state']) {
                throw new \Exception('传递的STATE参数不匹配！');
            }
        }
    }

    /**
     * Description:  默认获取AccessToken请求参数
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return array
     */
    protected function accessTokenParams(){
        $params = [
            'client_id'     => $this->config['app_id'],
            'client_secret' => $this->config['app_secret'],
            'grant_type'    => $this->config['grant_type'],
            'code'          => isset($_REQUEST['code']) ? $_REQUEST['code'] : '',
            'redirect_uri'  => $this->config['callback'],
        ];
        return $params;
    }

    /**
     * Description:  获取AccessToken
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    protected function getToken(){
        if (empty($this->token)) {
            /** 验证state参数 */
            $this->CheckState();

            /** 获取参数 */
            $params = $this->accessTokenParams();

            /** 获取access_token */
            $token =  $this->POST($this->AccessTokenURL, $params);
            /** 解析token值(子类实现此方法) */
            $this->token = $this->parseToken($token);
        }
    }

    /**
     * Description:  执行GET请求操作
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $url
     * @param array $params
     * @param array $headers
     * @return string
     */
    protected function get($url, $params = [], $headers = [])
    {
        return \tinymeng\tools\HttpRequest::httpGet($url, $params,$headers);
    }

    /**
     * Description:  执行POST请求操作
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $url
     * @param array $params
     * @param array $headers
     * @return mixed
     */
    protected function post($url, $params = [], $headers = [])
    {
        $headers[] = 'Accept: application/json';//GitHub需要的header
        return \tinymeng\tools\HttpRequest::httpPost($url, $params,$headers);
    }

    /**
     * 格式化性别参数
     * M代表男性,F代表女性
     * @param $gender
     */
    public function getGender($gender){
        return strtolower(substr($gender , 0 , 1)) == 'm' ? ConstCode::GENDER_MAN : ConstCode::GENDER_WOMEN;
    }
}
