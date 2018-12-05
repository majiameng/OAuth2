<?php

/**
 * 第三方登陆实例抽象类
 * @author: JiaMeng <666@majiameng.com>
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
        $gateway = Str::uFirst($gateway);
        $class = __NAMESPACE__ . '\\Gateways\\' . $gateway;
        if (class_exists($class)) {
            $app = new $class($config);
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
