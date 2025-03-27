<?php
/**
 * 第三方登陆实例抽象类
 * @author: JiaMeng <666@majiameng.com>
 */
namespace tinymeng\OAuth2;

use tinymeng\OAuth2\Connector\GatewayInterface;
use tinymeng\OAuth2\Exception\OAuthException;
use tinymeng\OAuth2\Helper\Str;
/**
 * @method static \tinymeng\OAuth2\Gateways\Alipay Alipay(array $config) 阿里云
 * @method static \tinymeng\OAuth2\Gateways\Wechat wechat(array $config) 微信
 * @method static \tinymeng\OAuth2\Gateways\Wechat weixin(array $config) 微信
 * @method static \tinymeng\OAuth2\Gateways\Qq Qq(array $config) QQ
 * @method static \tinymeng\OAuth2\Gateways\Facebook Facebook(array $config) Facebook
 * @method static \tinymeng\OAuth2\Gateways\Github Github(array $config) Github
 * @method static \tinymeng\OAuth2\Gateways\Google Google(array $config) Google
 * @method static \tinymeng\OAuth2\Gateways\Line Line(array $config) Line
 * @method static \tinymeng\OAuth2\Gateways\Sina Sina(array $config) Sina
 * @method static \tinymeng\OAuth2\Gateways\Twitter Twitter(array $config) Twitter
 * @method static \tinymeng\OAuth2\Gateways\Douyin Douyin(array $config) 抖音
 * @method static \tinymeng\OAuth2\Gateways\Baidu Baidu(array $config) 百度
 * @method static \tinymeng\OAuth2\Gateways\Coding Coding(array $config) Coding
 * @method static \tinymeng\OAuth2\Gateways\Csdn Csdn(array $config) CSDN
 * @method static \tinymeng\OAuth2\Gateways\Gitee Gitee(array $config) Gitee
 * @method static \tinymeng\OAuth2\Gateways\Gitlab GitLab(array $config) GitLab
 * @method static \tinymeng\OAuth2\Gateways\Oschina OSChina(array $config) OSChina
 * @method static \tinymeng\OAuth2\Gateways\Wecom Wecom(array $config) 企业微信
 * @method static \tinymeng\OAuth2\Gateways\Kuaishou Kuaishou(array $config) 快手
 */
abstract class OAuth
{

    /**
     * Description:  init
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $gateway
     * @param null $config
     * @return mixed
     * @throws OAuthException
     */
    protected static function init($gateway, $config)
    {
        if(empty($config)){
            throw new OAuthException("第三方登录 [$gateway] config配置不能为空");
        }
        $baseConfig = [
            'app_id'    => '',
            'app_secret'=> '',
            'callback'  => '',
            'scope'     => '',
            'type'      => '',
        ];
        if($gateway == 'weixin'){
            /** 兼容 tinymeng/oauth v1.0.0完美升级 */
            $gateway = 'wechat';
        }
        $gateway = Str::uFirst($gateway);
        $class = __NAMESPACE__ . '\\Gateways\\' . $gateway;
        if (class_exists($class)) {
            $app = new $class(array_replace_recursive($baseConfig,$config));
            if ($app instanceof GatewayInterface) {
                return $app;
            }
            throw new OAuthException("第三方登录基类 [$gateway] 必须继承抽象类 [GatewayInterface]");
        }
        throw new OAuthException("第三方登录基类 [$gateway] 不存在");
    }

    /**
     * Description:  __callStatic
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $gateway
     * @param $config
     * @return mixed
     */
    public static function __callStatic($gateway, $config)
    {
        return self::init($gateway, ...$config);
    }

}
