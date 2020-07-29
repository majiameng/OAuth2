<?php
/**
 * 第三方登陆实例抽象类
 * @author: JiaMeng <666@majiameng.com>
 * @method static \tinymeng\OAuth2\OAuth Alipay(array $config) 阿里云
 * @method static \tinymeng\OAuth2\OAuth Weixin(array $config) 微信
 * @method static \tinymeng\OAuth2\OAuth Qq.php(array $config) QQ
 * @method static \tinymeng\OAuth2\OAuth Facebook(array $config) Facebook
 * @method static \tinymeng\OAuth2\OAuth Github(array $config) Github
 * @method static \tinymeng\OAuth2\OAuth Google(array $config) Google
 * @method static \tinymeng\OAuth2\OAuth Line(array $config) Line
 * @method static \tinymeng\OAuth2\OAuth Sina(array $config) Sina
 * @method static \tinymeng\OAuth2\OAuth Twitter(array $config) Twitter
 */
namespace tinymeng\OAuth2;

use tinymeng\OAuth2\Connector\GatewayInterface;
use tinymeng\OAuth2\Helper\Str;
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
