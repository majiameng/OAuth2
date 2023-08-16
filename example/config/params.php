<?php
/**
 * 获取回调域名
 * 根据项目需求，以及配置到对应官方回调域名
 */
$hostInfo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
$callback = $hostInfo."/example/login";

/**
 * 配置文件
 * 参考文档：https://github.com/majiameng/OAuth2/wiki/Configuration
 */
$params = [
    'qq'=>[
        'app_id'        => '1014*****',
        'app_secret'    => '8a2b322610d7a0d****',
        'scope'         => 'get_user_info',
        'callback' => 'http://majiameng.com/app/qq',
        'is_unioid' => true //已申请unioid打通
    ],
    'wechat'=>[
        'pc'=>[
            'app_id' => 'wx52e2b2464*****',
            'app_secret' => 'd5dad705a1159d*********',
            'callback' => $callback,
            'scope'      => 'snsapi_login',//扫码登录
            //'proxy_url' => 'http://www.abc.com/wx_proxy.php',//如果不需要代理请注释此行
            //'proxy_url' => 'http://www.abc.com/weixin-authorize-proxy.html',//如果不需要代理请注释此行
        ],
        'mobile'=>[
            'app_id' => 'wx6ca7410f8******',
            'app_secret' => '30a206b87b7689b19f11******',
            'callback' => $callback,
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
    /**
     * 更多请查看参考文档：https://github.com/majiameng/OAuth2/wiki/Configuration
     * TODO...
     */
];
return $params;