<?php
/**
 * 获取回调域名
 * 根据项目需求，以及配置到对应官方回调域名
 */
$hostInfo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
$callback = $hostInfo."/example/login";

/**
 * 配置文件
 */
$params = [
    // 国内平台
    'qq' => [
        'app_id'     => '1014*****',
        'app_secret' => '8a2b322610d7a0d****',
        'scope'      => 'get_user_info',
        'callback'   => $callback,
        'is_unioid'  => true //已申请unioid打通
    ],
    'wechat' => [
        'pc' => [
            'app_id'     => 'wx52e2b2464*****',
            'app_secret' => 'd5dad705a1159d*********',
            'callback'   => $callback,
            'scope'      => 'snsapi_login', //扫码登录
        ],
        'mobile' => [
            'app_id'     => 'wx6ca7410f8******',
            'app_secret' => '30a206b87b7689b19f11******',
            'callback' => $callback,
            'scope'      => 'snsapi_userinfo',//静默授权=>snsapi_base;获取用户信息=>snsapi_userinfo
            //'proxy_url' => 'http://www.abc.com/wx_proxy.php',//如果不需要代理请注释此行
            //'proxy_url' => 'http://www.abc.com/weixin-authorize-proxy.html',//如果不需要代理请注释此行
        ],
        'app' => [
            'app_id'     => 'wx6ca7410f8******',
            'app_secret' => '30a206b87b7689b19f11******',
            'type'       => 'app',
        ],
        /**
         * 微信小程序只能获取到 openid session_key
         * 详见文档 https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/login/auth.code2Session.html
         */
        'applets' => [
            'app_id'     => 'wx6ca7410f8******',
            'app_secret' => '30a206b87b7689b19f11******',
            'type'       => 'applets',
        ],
    ],
    'wecom' => [ // 企业微信
        'app_id'     => 'ww1234567890******',
        'app_secret' => 'abcdefghijklmn******',
        'callback'   => $callback,
        'agent_id'   => '1000001', // 企业应用ID
    ],
    'sina' => [
        'app_id'     => '12345678',
        'app_secret' => 'abcdefghijklmn******',
        'callback'   => $callback,
        'scope'      => 'all',
    ],
    'alipay' => [
        'app_id'      => '2021********',
        'app_secret'  => 'MIIEvg******',
        'public_key'  => 'MIIBIj******',
        'callback'    => $callback,
        'scope'       => 'auth_user',
    ],
    'dingtalk' => [
        'app_id'     => 'dingoa******',
        'app_secret' => 'LpK3cq******',
        'callback'   => $callback,
        'scope'      => 'openid',
    ],
    'xiaomi' => [
        'app_id'     => '2882303******',
        'app_secret' => 'nFeTt2******',
        'callback'   => $callback,
        'scope'      => 'profile',
    ],
    'huawei' => [
        'app_id'     => '123456789',
        'app_secret' => 'abcdefg******',
        'callback'   => $callback,
        'scope'      => 'openid profile',
    ],

    // 开发平台
    'github' => [
        'app_id'     => 'd4df85******',
        'app_secret' => '4649f8****************',
        'callback'   => $callback,
        'scope'      => 'user',
    ],
    'gitlab' => [
        'app_id'     => '12345678',
        'app_secret' => 'abcdefgh******',
        'callback'   => $callback,
        'scope'      => 'read_user',
    ],
    'gitee' => [
        'app_id'     => '12345678',
        'app_secret' => 'abcdefgh******',
        'callback'   => $callback,
        'scope'      => 'user_info',
    ],
    'oschina' => [
        'app_id'     => '12345678',
        'app_secret' => 'abcdefgh******',
        'callback'   => $callback,
        'scope'      => 'user',
    ],

    // 国际平台
    'google' => [
        'app_id'     => '1234567890-abcdefg******',
        'app_secret' => 'GOCSPX-******',
        'callback'   => $callback,
        'scope'      => 'openid email profile',
    ],
    'facebook' => [
        'app_id'     => '1234567890',
        'app_secret' => 'abcdefgh******',
        'callback'   => $callback,
        'scope'      => 'public_profile,email',
    ],
    'twitter' => [
        'app_id'     => 'abcdefgh******',
        'app_secret' => '12345678******',
        'callback'   => $callback,
    ],
    'apple' => [
        'app_id'      => 'com.example.service',
        'team_id'     => 'ABCDEF123456',
        'key_id'      => 'ABCDEF123456',
        'private_key' => '-----BEGIN PRIVATE KEY-----\nMIIE...\n-----END PRIVATE KEY-----',
        'callback'    => $callback,
        'scope'       => 'name email',
    ],
    'microsoft' => [
        'app_id'     => '12345678-abcd-efgh-ijkl-mnopqrstuvwx',
        'app_secret' => 'abcdef~ghijklmno~pqrstuvwxyz~123456789',
        'callback'   => $callback,
        'scope'      => 'User.Read',
    ],
    'amazon' => [
        'app_id'     => 'amzn1.application-oa2-client.1234567890',
        'app_secret' => 'abcdefgh******',
        'callback'   => $callback,
        'scope'      => 'profile',
    ],
    'yahoo' => [
        'app_id'     => '12345678',
        'app_secret' => 'abcdefgh******',
        'callback'   => $callback,
        'scope'      => 'openid profile',
    ],
];

return $params;