<?php
/**
 * 获取回调域名
 * 根据项目需求，以及配置到对应官方回调域名
 */
$hostInfo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
$callback = $hostInfo."/connect.php";

/**
 * 配置文件
 */
$params = [
    // 国内平台
    /**
     * QQ现在可以获取`unionid`了，详见: http://wiki.connect.qq.com/unionid%E4%BB%8B%E7%BB%8D
     * 只需要配置参数`$config['withUnionid'] = true`，默认不会请求获取Unionid
     */
    'qq' => [
        'app_id'     => '1014*****',
        'app_secret' => '8a2b322610d7a0d****',
        'scope'      => 'get_user_info',
        'callback'   => $callback,
        'is_unioid'  => true //已申请unioid打通withUnionid
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
    'douyin'=>[
        //抖音官方：请确保授权回调域网站协议为 https
        'pc'=>[
            'oauth_type' => \tinymeng\OAuth2\Helper\ConstCode::TYPE_DOUYIN,//抖音douyin，头条toutiao，西瓜xigua，使用\tinymeng\OAuth2\Helper\ConstCode
            'app_id' => 'awenvxxxxxxxx7x4',
            'app_secret' => '5cfbc25badxxxxxxxc7be8c1',
            'callback' => 'https://majiameng.com/app/douyin',
            'scope'      => 'trial.whitelist,user_info',//trial.whitelist为白名单人员权限,上线后删掉
            'optionalScope' => '',//应用授权可选作用域,多个授权作用域以英文逗号（,）分隔，每一个授权作用域后需要加上一个是否默认勾选的参数，1为默认勾选，0为默认不勾选
        ],
        'mobile'=>[
            //待完善TODO...
            'app_id' => 'awenvxxxxxxxx7x4',
            'app_secret' => '5cfbc25badxxxxxxxc7be8c1',
            'callback' => 'https://majiameng.com/app/douyin',
            'scope'      => 'login_id',//login_id为静默授权
        ],
        'app'=>[
            //待完善TODO...
        ],
        'applets'=>[
            //待完善TODO...
            'app_id'=>'awenvxxxxxxxx7x4',
            'app_secret'=>'5cfbc2sssadxxxxxxxc7be8c1',
        ],
    ],
    /**
     * 支付宝增加open_id废弃user_id https://opendocs.alipay.com/mini/0ai2i6?pathHash=13dd5946
     * 支付宝unionid布局 https://opendocs.alipay.com/mini/0ai2i8?pathHash=9e717ecc
     */
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
    /**
     * > scope 有两个值
     * > 获取用户信息:  'scope'      => 'https://www.googleapis.com/auth/userinfo.profile',
     * > 获取用户email: 'scope'      => 'https://www.googleapis.com/auth/userinfo.email',
     */
    'google' => [
        'app_id'     => '1234567890-abcdefg******',
        'app_secret' => 'GOCSPX-******',
        'callback'   => $callback,
        'scope'      => 'https://www.googleapis.com/auth/userinfo.profile',
    ],
    /**
     * facebook有个特殊的配置`$config['field']`，默认是`'id,name,gender,picture.width(400)'`，你可以根据需求参考官方文档自行选择要获取的用户信息
     */
    'facebook' => [
        'app_id'     => '1234567890',
        'app_secret' => 'abcdefgh******',
        'callback'   => $callback,
        'scope'      => 'public_profile,user_gender',//user_gender需要审核，所以不一定能获取到
    ],
    'twitter' => [
        'app_id'     => 'abcdefgh******',
        'app_secret' => '12345678******',
        'callback'   => $callback,
    ],
    'line'=>[
        'app_id'     => '20074****',
        'app_secret' => '26db81744466b8d8b4*****',
        'scope'      => 'profile',
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

/**
 * 打通unionid的话需要将公众号绑定到同一个微信开放平台
 * 会返回的唯一凭证unionid字段
 */


/**
 * 如果需要微信代理登录(微信app内登录)，则需要：
 * 1.将 example/wx_proxy.php 放置在微信公众号设定的回调域名某个地址，如 http://www.abc.com/proxy/wx_proxy.php
 * 2.config中加入配置参数proxy_url，地址为 http://www.abc.com/proxy/wx_proxy.php
 * 如下所示
 */
//$config['proxy_url'] = 'http://www.abc.com/proxy/wx_proxy.php';

return $params;