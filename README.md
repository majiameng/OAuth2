<h1 align="center">tinymeng/oauth</h1>
<p align="center">
<a href="https://scrutinizer-ci.com/g/majiameng/OAuth2/?branch=master"><img src="https://scrutinizer-ci.com/g/majiameng/OAuth2/badges/quality-score.png?b=master" alt="Scrutinizer Code Quality"></a>
<a href="https://scrutinizer-ci.com/g/majiameng/OAuth2/build-status/master"><img src="https://scrutinizer-ci.com/g/majiameng/OAuth2/badges/build.png?b=master" alt="Build Status"></a>
<a href="https://packagist.org/packages/tinymeng/oauth"><img src="https://poser.pugx.org/tinymeng/oauth/v/stable" alt="Latest Stable Version"></a>
<a href="https://github.com/majiameng/OAuth2/tags"><img src="https://poser.pugx.org/tinymeng/oauth/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/tinymeng/oauth"><img src="https://poser.pugx.org/tinymeng/oauth/v/unstable" alt="Latest Unstable Version"></a>
<a href="https://github.com/majiameng/OAuth2/blob/master/LICENSE"><img src="https://poser.pugx.org/tinymeng/oauth/license" alt="License"></a>
</p>

After developing multiple QQ and WeChat logins, it is natural to develop an aversion. Inertia has returned and I want to search for relevant wheels online, but I have never found a one that I feel satisfied with. Either it is too difficult to understand when using, or the file structure is too messy, so I have to roll up my sleeves and do it myself.

**！！Please familiarize yourself with the Oauth/QQ/WeChat documentation first!! Please have basic debugging skills ！！**

Welcome Star, welcome PR ！

> If you have any questions to communicate, please post them here ： [OAuth2](https://github.com/majiameng/OAuth2/issues/1) exchange Or Send an email 666@majiameng.com

Integrating many third party login interfaces, including qq-login、wx-login、sina-login、github-login、alipay-login、aliyum-login、douyin-login and so on

集成了许多第三方登录界面，包括QQ登录、微信登录、新浪登录、github登录、支付宝登录、百度登录、抖音登录、GitLab、Naver、Line、codeing、csdn、gitee等，陆续增加ing

# Documentation

## You can find the tinymeng/oauth documentation on the website. Check out the Getting Started page for a quick overview.

* [Wiki Home](https://github.com/majiameng/OAuth2/wiki)
* [Getting Started](https://github.com/majiameng/OAuth2/wiki/Getting-Started)
* [Installation](https://github.com/majiameng/OAuth2/wiki/Installation)
* [Configuration](https://github.com/majiameng/OAuth2/wiki/Configuration)
* [Contributing Guide](https://github.com/majiameng/OAuth2/wiki/Contributing-Guide)
* [Update log](https://github.com/majiameng/OAuth2/wiki/Update-log)
* [中文文档](https://github.com/majiameng/OAuth2/wiki/zh-cn-Home)

## General third-party login instructions document

| Gateways |            Login Name            |          Login Method           |
|:--------:|:--------------------------------:|:-------------------------------:|
|    qq    |               腾讯QQ               |        PC Scan Code、App         |
|  wechat  |                微信                | PC、 Official account、Applet、App |
|   sina   |               新浪微博               |             PC、APP              |
|  alipay  |               支付宝                |             PC、APP              |
|  aliyun  |               阿里云                |               PC                |
|  github  |              GitHub              |               PC                |
|  google  |             谷歌google             |               PC                |
| facebook |                脸书                |               PC                |
|  naver   |              Naver               |               PC                |
| twitter  |             twitter              |               PC                |
|   line   |               line               |               PC                |
|  douyin  | 抖音 Douyin 、 头条 toutiao 、西瓜 xigua |             PC、APP              |
|  baidu   |            百度             |               PC                |
|  coding  |          Coding           |               PC                |
|   csdn   |           CSDN            |               PC                |
|  gitee   |           Gitee           |               PC                |
|  gitlab  |          GitLab           |               PC                |
| oschina  |          OSChina          |               PC                |


> Pay attention to ：Google、facebook、twitter These foreign platforms require overseas or HK servers to successfully callback

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

## Star History

[![Star History Chart](https://api.star-history.com/svg?repos=majiameng/OAuth2&type=Date)](https://github.com/majiameng/OAuth2)


