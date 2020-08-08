<?php
/**
 * 第三方登陆实例抽象类
 * @author: JiaMeng <666@majiameng.com>
 */
namespace tinymeng\OAuth2;

use tinymeng\OAuth2\Connector\GatewayInterface;
use tinymeng\OAuth2\Helper\Str;
/**
 * @method static \tinymeng\OAuth2\Gateways\Alipay Alipay(array $config) 阿里云
 * @method static \tinymeng\OAuth2\Gateways\Wechat wechat(array $config) 微信
 * @method static \tinymeng\OAuth2\Gateways\Qq Qq(array $config) QQ
 * @method static \tinymeng\OAuth2\Gateways\Facebook Facebook(array $config) Facebook
 * @method static \tinymeng\OAuth2\Gateways\Github Github(array $config) Github
 * @method static \tinymeng\OAuth2\Gateways\Google Google(array $config) Google
 * @method static \tinymeng\OAuth2\Gateways\Line Line(array $config) Line
 * @method static \tinymeng\OAuth2\Gateways\Sina Sina(array $config) Sina
 * @method static \tinymeng\OAuth2\Gateways\Twitter Twitter(array $config) Twitter
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
     * @throws \Exception
     */
    protected static function init($gateway, $config = null)
    {
        $baseConfig = [
            'app_id'    => '',
            'app_secret'=> '',
            'callback'  => '',
            'scope'     => '',
        ];
        $gateway = Str::uFirst($gateway);
        $class = __NAMESPACE__ . '\\Gateways\\' . $gateway;
        if (class_exists($class)) {
            $app = new $class(array_replace_recursive($baseConfig,$config));
            if ($app instanceof GatewayInterface) {
                return $app;
            }
            throw new \Exception("第三方登录基类 [$gateway] 必须继承抽象类 [GatewayInterface]");
        }
        throw new \Exception("第三方登录基类 [$gateway] 不存在");
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
