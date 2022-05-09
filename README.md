# Integrating many third party login interfaces, including qq-login、wx-login、sina-login、github-login、alipay-login and so on

# 通用第三方登录说明文档

* 微信网页扫码、微信公众号、微信小程序、微信App
* QQ
* 微博
* 支付宝
* GitHub
* Google
* Facebook
* Naver
* Twitter
* Line

* 所有Web和App登录

### 安装

```
composer require tinymeng/oauth:^2.0.0 -vvv
```

> 类库使用的命名空间为`\\tinymeng\\oauth`

### 目录结构

```
.
├── example                          代码源文件目录
│   └── wx_proxy.php                微信多域名代理php代码版
│   └── weixin-authorize-proxy.html 微信多域名代理html代码版,推荐使用html版
├── src                              代码源文件目录
│   ├── Connector
│   │   ├── Gateway.php            必须继承的抽象类
│   │   └── GatewayInterface.php   必须实现的接口
│   ├── Gateways
│   │   ├── Alipay.php
│   │   ├── Facebook.php
│   │   ├── Github.php
│   │   ├── Google.php
│   │   ├── Line.php
│   │   ├── Naver.php
│   │   ├── Qq.php
│   │   ├── Twitter.php
│   │   ├── Sina.php
│   │   └── Wechat.php
│   ├── Helper
│   │   ├── ConstCode.php          公共常量
│   │   └── Str.php                字符串辅助类
│   └── OAuth.php                   抽象实例类
├── composer.json                    composer文件
├── LICENSE                          MIT License
└── README.md                        说明文件
```


### 公共方法

在接口文件中，定义了4个方法，是每个第三方基类都必须实现的，用于相关的第三方登录操作和获取数据。方法名如下：

```php

    /**
     * Description:  得到跳转地址
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return mixed
     */
    public function getRedirectUrl();

    /**
     * Description:  获取当前授权用户的openid标识
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return mixed
     */
    public function openid();

    /**
     * Description:  获取格式化后的用户信息
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return mixed
     */
    public function userInfo();

    /**
     * Description:  获取原始接口返回的用户信息
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return mixed
     */
    public function getUserInfo();
    
```

### 典型用法

以ThinkPHP5为例

```php
<?php
namespace app\index\controller;

use think\Config;
use tinymeng\OAuth2\OAuth;
use tinymeng\OAuth2\Helper\Str;
use tinymeng\tools\Tool;

class Login extends Common
{
    protected $config;

    /**
     * Description:  登录
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return mixed
     */
    public function index()
    {
        $name="qq";//登录类型,例:qq / google
        if (empty(input('get.'))) {
            /** 登录 */
            $result = $this->login($name);
            $this->redirect($result);
        }
        /** 登录回调 */
        $this->callback($name);
        return $this->fetch('index');
    }
    
    /**
     * Description:  获取配置文件
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $name
     */
    public function getConfig($name){
        //可以设置代理服务器，一般用于调试国外平台
        //$this->config['proxy'] = 'http://127.0.0.1:1080';
        
        $this->config = Config::get($name);
        if($name == 'wechat'){
            if(!Tool::isMobile()){
                $this->config = $this->config['pc'];//微信pc扫码登录
            }elseif(Tool::isWeiXin()){
                $this->config = $this->config['mobile'];//微信浏览器中打开
            }else{
                echo '请使用微信打开!';exit();//手机浏览器打开
            }
        }
        //$this->config['state'] = Str::random();//如需手动验证state,请开启此行并存储state值
    }
    
    /**
     * Description:  登录链接分配，执行跳转操作
     * Author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    public function login($name){
        /** 获取配置 */
        $this->getConfig($name);

        /** 初始化实例类 */
        $oauth = OAuth::$name($this->config);
        $oauth->mustCheckState();//如需手动验证state,请关闭此行
        if(Tool::isMobile() || Tool::isWeiXin()){
            /**
             * 对于微博，如果登录界面要适用于手机，则需要设定->setDisplay('mobile')
             * 对于微信，如果是公众号登录，则需要设定->setDisplay('mobile')，否则是WEB网站扫码登录
             * 其他登录渠道的这个设置没有任何影响，为了统一，可以都写上
             */
            $oauth->setDisplay('mobile');
        }
        return $oauth->getRedirectUrl();
    }
    
    /**
     * Description:  登录回调
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $name
     * @return bool
     */
    public function callback($name)
    {
        /** 获取配置 */
        $this->getConfig($name);

        /** 初始化实例类 */
        $oauth = OAuth::$name($this->config);
        $oauth->mustCheckState();//如需手动验证state,请关闭此行

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

        //获取登录类型
        $userInfo['type'] = \tinymeng\OAuth2\Helper\ConstCode::getTypeConst($userInfo['channel']);

        var_dump($userInfo);die;
        
    }
}
```

通过系统自动设置state，如有需要请自行处理验证，state也放入config里即可
Line和Facebook强制要求传递state，如果你没有设置，则会传递随机值
如果要验证state，则在获取用户信息的时候要加上`->mustCheckState()`方法。
```php
$name = "qq";
$snsInfo = OAuth::$name($this->config)->mustCheckState()->userinfo();
```
> 注意，不是所有的平台都支持传递state，请自行阅读官方文档链接,各个文档在实现类里有说明.


微信有一个额外的方法，用于获取代理请求的地址

```php
    /**
     * 获取中转代理地址
     */
    public function getProxyURL();
```

App登录回调
```php
    $name = "qq";
    /**
     * 回调中如果是App登录
     */
    $type = 'app';
    $userInfo = OAuth::$name($this->config)->setType($type)->userInfo();
    //->setType() 或者  在配置文件中设置config['type'] = 'app'


    /**
    * access_token 通过$_REQUEST['access_token'] 进行传值到oauth中
    *    facebook App登录
    *    qq App登录
    *    wechat App登录
    */

    /**
    * code 通过$_REQUEST['code'] 进行传值到oauth中
    *    google App登录
    */
```



### 配置文件样例

#### 1.微信

```
'wechat'=>[
    'pc'=>[
        'app_id' => 'wx52e2b2464*****',
        'app_secret' => 'd5dad705a1159d*********',
        'callback' => 'http://majiameng.com/app/wechat',
        'scope'      => 'snsapi_login',//扫码登录
        //'proxy_url' => 'http://www.abc.com/wx_proxy.php',//如果不需要代理请注释此行
        //'proxy_url' => 'http://www.abc.com/weixin-authorize-proxy.html',//如果不需要代理请注释此行
    ],
    'mobile'=>[
        'app_id' => 'wx6ca7410f8******',
        'app_secret' => '30a206b87b7689b19f11******',
        'callback' => 'http://majiameng.com/app/wechat',
        'scope'      => 'snsapi_userinfo',//静默授权=>snsapi_base;获取用户信息=>snsapi_userinfo
        //'proxy_url' => 'http://www.abc.com/wx_proxy.php',//如果不需要代理请注释此行
        //'proxy_url' => 'http://www.abc.com/weixin-authorize-proxy.html',//如果不需要代理请注释此行
    ],
    'app'=>[
        'app_id' => 'wx6ca7410f8******',
        'app_secret' => '30a206b87b7689b19f11******',
        'type'      => 'app',//登录类型app
    ],
    /**
     * 微信小程序只能获取到 openid session_key
     * 详见文档 https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/login/auth.code2Session.html
     */
    'applets'=>[
        'app_id' => 'wx6ca7410f8******',
        'app_secret' => '30a206b87b7689b19f11******',
        'type'      => 'applets',//登录类型小程序
    ],
]
```
> 打通unionid的话需要将公众号绑定到同一个微信开放平台
会返回的唯一凭证unionid字段

```
/**
 * 如果需要微信代理登录(微信app内登录)，则需要：
 * 1.将 example/wx_proxy.php 放置在微信公众号设定的回调域名某个地址，如 http://www.abc.com/proxy/wx_proxy.php
 * 2.config中加入配置参数proxy_url，地址为 http://www.abc.com/proxy/wx_proxy.php
 * 如下所示
 */
//$config['proxy_url'] = 'http://www.abc.com/proxy/wx_proxy.php';
```


#### 2.QQ

```
'qq'=>[
    'app_id'        => '1014*****',
    'app_secret'    => '8a2b322610d7a0d****',
    'scope'         => 'get_user_info',
    'callback' => 'http://majiameng.com/app/qq',
    'is_unioid' => true //已申请unioid打通
]
```
QQ现在可以获取`unionid`了，详见: http://wiki.connect.qq.com/unionid%E4%BB%8B%E7%BB%8D
只需要配置参数`$config['withUnionid'] = true`，默认不会请求获取Unionid

#### 3.微博

```
'app_id'     => '78734****',
'app_secret' => 'd8a00617469018d61c**********',
'callback' => 'http://majiameng.com/app/sina',
'scope'      => 'all',
```

#### 4.GitHub

```
'application_name' => '佳萌驿站',
'app_id'      => 'a56b04a5********',
'app_secret' => '93ae7e5b137c6228e******************',
'callback' => 'http://majiameng.com/app/github',
```

#### 5.支付宝

```
'app_id'      => '2016052*******',
'scope'       => 'auth_user',
'aes' => 'asdf*******************==',// AES密钥
'callback' => 'http://majiameng.com/app/alipay',
'pem_private' => '/config/cert/rsaPrivateKey.pem', // 你的私钥
'pem_public'  => '/config/cert/alipayrsaPublicKey.pem', // 支付宝公钥
'is_sandbox' => false,   //是否是沙箱环境
```

#### 6.Facebook

```
'app_id'     => '2774925********',
'app_secret' => '99bfc8ad35544d7***********',
'scope'      => 'public_profile,user_gender',//user_gender需要审核，所以不一定能获取到
```

facebook有个特殊的配置`$config['field']`，默认是`'id,name,gender,picture.width(400)'`，你可以根据需求参考官方文档自行选择要获取的用户信息

#### 7.Twitter

```
'app_id'     => '3nHCxZgcK1WpYV**********',
'app_secret' => '2byVAPayMrG8LISjopwIMcJGy***************',
```

#### 8.Line

```
'app_id'     => '159******',
'app_secret' => '1f19c98a61d148f2************',
'scope'      => 'profile',
```

#### 8.Naver

```
'app_id'     => 'OTRf******',
'app_secret' => 'hAc5****',
'callback' => 'http://majiameng.com/app/naver',
```

#### 9.Google

```
'app_id'     => '7682717*******************.apps.googleusercontent.com',
'app_secret' => 'w0Kq-aYA***************',
'scope'      => 'https://www.googleapis.com/auth/userinfo.profile',
'callback' => 'http://majiameng.com/app/google',
```
> scope 有两个值 
> 获取用户信息:  'scope'      => 'https://www.googleapis.com/auth/userinfo.profile',
> 获取用户email: 'scope'      => 'https://www.googleapis.com/auth/userinfo.email',


### `userinfo()`公共返回样例
```
Array
(
    [open_id] => 1047776979*******   //部分登录此字段值是access_token(例:sina/google),如做唯一请使用union_id字段
    [union_id] => 444444445*******  //用户的唯一凭证
    [channel] => 1;                 //登录类型请查看 \tinymeng\OAuth2\Helper\ConstCode
    [nickname] => 'Tinymeng'        //昵称
    [gender] => 1;                  //0=>未知 1=>男 2=>女   twitter和line不会返回性别，所以这里是0，Facebook根据你的权限，可能也不会返回，所以也可能是0
    [avatar] => http://thirdqq.qlogo.cn/qqapp/101426434/50D523803F5B51AAC01616105161C7B1/100 //头像
    [type] => 21;                   //登录子类型请查看 \tinymeng\OAuth2\Helper\ConstCode ，例如：channel：微信 type：小程序或app
)
```
> 部分登录类型还会返回个别数据,如需返回原数据请使用 `getUserInfo()` 方法



### 版本修复

2022-05-09 更新以下功能
Tag v2.0.9
```
1.解决bug：获取用户信息post方式，导致code获取不到
```

2022-05-07 更新以下功能
Tag v2.0.8
```
1.兼容微信小程序获取openid处理
接口文档： https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/login/auth.code2Session.html
```

2021-03-01 更新以下功能
Tag v2.0.7
```
1.兼容所有性别处理
2.添加naver第三方登录
```

2021-02-07 更新以下功能
Tag v2.0.6
```
1.修复Google应用APP登录
```

2021-02-07 更新以下功能
Tag v2.0.5
```
1.修复各个应用APP登录
```

2020-11-04 更新以下功能
Tag v2.0.4
```
1.修复微信登录代理bug
2.完善Readme
```


> 大家如果有问题要交流，就发在这里吧： [OAuth2](https://github.com/majiameng/OAuth2/issues/1) 交流 或发邮件 666@majiameng.com
