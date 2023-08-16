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
|  baidu   |            百度(开发ing)             |               PC                |
|  coding  |          Coding(开发ing)           |               PC                |
|   csdn   |           CSDN(开发ing)            |               PC                |
|  gitee   |           Gitee(开发ing)           |               PC                |
|  gitlab  |          GitLab(开发ing)           |               PC                |
| oschina  |          OSChina(开发ing)          |               PC                |


> Pay attention to ：Google、facebook、twitter These foreign platforms require overseas or HK servers to successfully callback

### Installation

```
composer require tinymeng/oauth:^2.0.0 -vvv
```

> The namespace used by the class library is `\\tinymeng\\oauth`

### 目录结构

```
.
├── example                         Code source file directory
│   └── wx_proxy.php                WeChat Multi Domain Agent PHP Code Version
│   └── weixin-authorize-proxy.html WeChat multi domain proxy HTML code version, recommended to use HTML version
├── src                             Code source file directory
│   ├── Connector
│   │   ├── Gateway.php             Abstract classes that must be inherited
│   │   └── GatewayInterface.php    Interface that must be implemented
│   ├── Gateways
│   │   ├── Alipay.php
│   │   ├── Douyin.php
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
│   │   ├── ConstCode.php           Common constant
│   │   └── Str.php                 String auxiliary class
│   └── OAuth.php                   Abstract instance class
├── composer.json                   Composer File
├── LICENSE                         MIT License
└── README.md                       Documentation
```

## Star History

[![Star History Chart](https://api.star-history.com/svg?repos=majiameng/OAuth2&type=Date)](https://github.com/majiameng/OAuth2)



### 如这些都懒得去申请资质以及想更简单的接入，下面会对你有帮助
[微梦聚合快捷登录平台](https://oauth.bjwmsc.com/)


[微梦登录demo](https://oauth.bjwmsc.com/demo/)

> 1.微梦聚合快捷登录中转API 是一款社会化账号聚合登录系统，让网站的最终用户可以一站式选择使用包括微信、微博、QQ、百度等多种社会化帐号登录该站点。

> 2.简化用户注册登录过程、改善用户浏览站点的体验、迅速提高网站注册量和用户数据量。有完善的开发文档与SDK，方便开发者快速接入。

> 3.可快捷接入标有【彩虹聚合登录】、【Oauth聚合登录】、【聚合登录】等项目平台。

> 4.不需要具备oauth开发资质和申请流程。
