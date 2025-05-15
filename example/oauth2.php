<?php
use tinymeng\tools\Tool;
use tinymeng\OAuth2\OAuth;

/**
 *
 */
class oauth2{

    /**
     * 全部配置文件
     * @var
     */
    public $configAll;
    /**
     * 单类型配置文件
     * @var
     */
    public $config;
    /**
     * 登录类型
     * @var
     */
    public $name;

    /**
     * @param $conf
     */
    public function __construct($conf){
        $this->configAll = $conf;
    }


    /**
     * Description:  获取配置文件
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $name
     */
    public function getConfig($name){
        $this->config = $this->configAll[$name]??[];
        if($name == 'wechat'){
            if(!Tool::isMobile()){
                $this->config = $this->config['pc'];//微信pc扫码登录
            }elseif(Tool::isWeiXin()){
                $this->config = $this->config['mobile'];//微信浏览器中打开
            }else{
                echo '请使用微信打开!';exit();//手机浏览器打开
            }
        }
        //可以设置代理服务器，一般用于调试国外平台
        //$this->config['proxy'] = 'http://127.0.0.1:1080';
    }

    /**
     * Description:  登录链接分配，执行跳转操作
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    public function login($name,$state=""){
        /** 获取配置 */
        if($name == 'wx') $name = 'wechat';
        $this->getConfig($name);
        $this->config['state'] = $state;

        /** 初始化实例类 */
        $oauth = OAuth::$name($this->config);
        if(Tool::isMobile() || Tool::isWeiXin()){
            /**
             * 对于微博，如果登录界面要适用于手机，则需要设定->setDisplay('mobile')
             * 对于微信，如果是公众号登录，则需要设定->setDisplay('mobile')，否则是WEB网站扫码登录
             * 其他登录渠道的这个设置没有任何影响，为了统一，可以都写上
             */
            $oauth->setDisplay('mobile');
        }

        $login_url = $oauth->getRedirectUrl();
        $result = [
            'code'=>0,
            'msg'=>'succ',
            'type'=>$name,
            'url'=>$login_url
        ];
        return $result;
    }

    /**
     * Description:  登录回调
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $name
     * @return array
     */
    public function callback($name)
    {
        /** 获取配置 */
        if($name == 'wx') $name = 'wechat';
        $this->getConfig($name);

        /** 初始化实例类 */
        $oauth = OAuth::$name($this->config);

        /** 获取第三方用户信息 */
        $userInfo = $oauth->userInfo();
        /**
         * 如果是App登录
         * $type = "applets";
         * $userInfo = OAuth::$name($this->config)->setType($type)->userInfo();
         */
        /**
         * 如果是App登录
         * $type = "applets";
         * $userInfo = OAuth::$name($this->config)->setType($type)->userInfo();
         */

        // 获取登录类型
        $userInfo['type'] = \tinymeng\OAuth2\Helper\ConstCode::getTypeConst($userInfo['channel']);

        // 处理用户信息
        $userInfo = $this->handleUserInfo($userInfo);
        $result = [
            'code'=>0,
            'msg'=>'succ',
            'type'=>$name,
            'userInfo'=>$userInfo
        ];
        return $result;
    }

    /**
     * 处理用户信息
     * @param $userInfo
     * @return mixed
     */
    public function handleUserInfo($userInfo)
    {
        /**
         * TODO... 存储用户信息到数据库，请自行完善代码
         */
        return $userInfo;
    }

}