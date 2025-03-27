<h1 align="center">tinymeng/oauth</h1>
<p align="center">
<a href="https://scrutinizer-ci.com/g/majiameng/OAuth2/?branch=master"><img src="https://scrutinizer-ci.com/g/majiameng/OAuth2/badges/quality-score.png?b=master" alt="Scrutinizer Code Quality"></a>
<a href="https://scrutinizer-ci.com/g/majiameng/OAuth2/build-status/master"><img src="https://scrutinizer-ci.com/g/majiameng/OAuth2/badges/build.png?b=master" alt="Build Status"></a>
<a href="https://packagist.org/packages/tinymeng/oauth"><img src="https://poser.pugx.org/tinymeng/oauth/v/stable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/tinymeng/oauth"><img src="https://poser.pugx.org/tinymeng/oauth/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/tinymeng/oauth"><img src="https://poser.pugx.org/tinymeng/oauth/v/unstable" alt="Latest Unstable Version"></a>
<a href="https://packagist.org/packages/tinymeng/oauth"><img src="https://poser.pugx.org/tinymeng/oauth/license" alt="License"></a>
</p>

开发了多次QQ与微信登录后，很自然产生一种反感，惰性又来了，想在网上找相关的轮子，可是一直没有找到一款自己觉得逞心如意的，要么使用起来太难理解，要么文件结构太杂乱，只有自己撸起袖子干了。

**！！请先熟悉 Oauth/QQ/微信 说明文档！！请具有基本的 debug 能力！！**

欢迎 Star，欢迎 PR！

> 大家如果有问题要交流，就发在这里吧： [OAuth2](https://github.com/majiameng/OAuth2/issues/1) 交流 或发邮件 666@majiameng.com

集成了许多第三方登录界面，包括QQ登录、微信登录、新浪登录、github登录、支付宝登录、百度登录、抖音登录、GitLab、Naver、Line、codeing、csdn、gitee等，陆续增加ing

# Documentation

## 您可以在网站上找到tinymeng/oauth文档。查看“入门”页面以获取快速概述。

* [Wiki Home](https://github.com/majiameng/OAuth2/wiki)
* [中文文档](https://github.com/majiameng/OAuth2/wiki/zh-cn-Home)
* [开始](https://github.com/majiameng/OAuth2/wiki/zh-cn-Getting-Started)
* [安装](https://github.com/majiameng/OAuth2/wiki/zh-cn-Installation)
* [配置文件](https://github.com/majiameng/OAuth2/blob/master/example/config/params.php)
* [贡献指南](https://github.com/majiameng/OAuth2/wiki/zh-cn-Contributing-Guide)
* [更新日志](https://github.com/majiameng/OAuth2/wiki/zh-cn-Update-log)

## 通用第三方登录说明文档

### 国内平台 (Domestic Platforms)

| Gateways  | Login Name | Login Method  |
|:---------:|:----------:|:-------------:|
| qq        | 腾讯QQ      | PC扫码、APP     |
| wechat    | 微信        | PC、公众号、小程序、APP |
| wecom     | 企业微信      | PC、APP        |
| sina      | 新浪微博      | PC、APP        |
| alipay    | 支付宝       | PC、APP        |
| aliyun    | 阿里云       | PC            |
| baidu     | 百度        | PC            |
| douyin    | 抖音        | PC、APP        |
| toutiao   | 头条        | PC、APP        |
| xigua     | 西瓜视频      | PC、APP        |
| dingtalk  | 钉钉        | PC、APP        |
| xiaomi    | 小米        | PC、APP        |
| huawei    | 华为        | PC、APP        |

### 开发平台 (Development Platforms)

| Gateways | Login Name | Login Method |
|:--------:|:----------:|:------------:|
| github   | GitHub     | PC           |
| gitlab   | GitLab     | PC           |
| gitee    | Gitee      | PC           |
| coding   | Coding     | PC           |
| oschina  | OSChina    | PC           |
| csdn     | CSDN       | PC           |

### 国际平台 (International Platforms)

| Gateways  | Login Name | Login Method |
|:---------:|:----------:|:------------:|
| google    | Google     | PC           |
| facebook  | Facebook   | PC           |
| twitter   | Twitter    | PC           |
| line      | Line       | PC           |
| naver     | Naver      | PC           |
| amazon    | Amazon     | PC           |
| apple     | Apple      | PC、APP       |
| yahoo     | Yahoo      | PC           |
| microsoft | Microsoft  | PC           |

> 注意事项 (Notes)：
> 1. Google、Facebook、Twitter 等国际平台需要使用海外或香港服务器才能正常回调
> 2. 部分平台支持多种授权方式，如 PC 网页授权、APP 授权等
> 3. 使用前请先阅读相应平台的开发文档并完成开发者资质认证

> 注：Google、facebook、twitter等这些国外平台需要海外或者HK服务器才能回调成功

### Installation

```
composer require tinymeng/oauth:^2.0.0 -vvv
```

> The namespace used by the class library is `\\tinymeng\\oauth`

### 目录结构

```
.
├── example                         示例代码目录
│   ├── config                      配置示例
│   │   └── params.php              参数配置示例
│   ├── oauth2.php                  OAuth2 使用示例
│   ├── proxy                       代理相关示例
│   │   └── wx_proxy.php            微信多域名代理 PHP 版本
│   └── weixin-authorize-proxy.html 微信多域名代理 HTML 版本(推荐)
├── src                             源代码目录
│   ├── Connector                   连接器基类目录
│   │   ├── Gateway.php             必须继承的抽象基类
│   │   └── GatewayInterface.php    必须实现的接口
│   ├── Exception                   异常处理目录
│   │   └── OAuthException.php      OAuth 异常类
│   ├── Gateways                    各平台授权实现目录
│   │   ├── Alipay.php              支付宝授权
│   │   ├── Aliyun.php              阿里云授权
│   │   ├── Baidu.php               百度授权
│   │   ├── Coding.php              Coding 授权
│   │   ├── Csdn.php                CSDN 授权
│   │   ├── Douyin.php              抖音授权
│   │   ├── Facebook.php            Facebook 授权
│   │   ├── Gitee.php               Gitee 授权
│   │   ├── Github.php              GitHub 授权
│   │   ├── Gitlab.php              GitLab 授权
│   │   ├── Google.php              Google 授权
│   │   ├── Line.php                Line 授权
│   │   ├── Naver.php               Naver 授权
│   │   ├── Oschina.php             OSChina 授权
│   │   ├── Qq.php                  QQ 授权
│   │   ├── Sina.php                新浪微博授权
│   │   ├── Twitter.php             Twitter 授权
│   │   └── Wechat.php              微信授权
│   ├── Helper                      辅助类目录
│   │   ├── ConstCode.php           公共常量定义
│   │   └── Str.php                 字符串辅助类
│   └── OAuth.php                   OAuth 工厂类
├── tests                           测试目录
│   └── OAuthTest.php               OAuth 测试类
├── composer.json                   Composer 配置文件
├── LICENSE                         MIT 开源协议
└── README.md                       说明文档
```


### Configuration
[Configuration](https://github.com/majiameng/OAuth2/wiki/Configuration)
```
Config::get($name)  获取对应登录类型的配置
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

> 打通unionid的话需要将公众号绑定到同一个微信开放平台
会返回的唯一凭证unionid字段


### `userinfo()`公共返回样例
```
Array
(
    [open_id] => 1047776979*******   //open_id数据唯一凭证
    [access_token] => 444444445*******  //用户的access_token凭证
    [union_id] => 444444445*******  //用户的唯一凭证（在同一平台下多设备参数返回一致）,部分登录此字段值是open_id(例:sina/google),
    [channel] => 1;                 //登录类型请查看 \tinymeng\OAuth2\Helper\ConstCode
    [nickname] => 'Tinymeng'        //昵称
    [gender] => 1;                  //0=>未知 1=>男 2=>女   twitter和line不会返回性别，所以这里是0，Facebook根据你的权限，可能也不会返回，所以也可能是0
    [avatar] => http://thirdqq.qlogo.cn/qqapp/101426434/50D523803F5B51AAC01616105161C7B1/100 //头像
    [type] => 21;                   //登录子类型请查看 \tinymeng\OAuth2\Helper\ConstCode ，例如：channel：微信 type：小程序或app
)
```
> 部分登录类型还会返回个别数据,如需返回原数据请使用 `getUserInfo()` 方法



## Star History

[![Star History Chart](https://api.star-history.com/svg?repos=majiameng/OAuth2&type=Date)](https://github.com/majiameng/OAuth2)

