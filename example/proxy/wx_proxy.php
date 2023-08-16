<?php
/**
 * Class WxProxy
 * @Author: TinyMeng <666@majiameng.com>
 * @Created: 2018/11/4
 */
class WxProxy
{
    /** @var string  */
    protected $AuthorizeURL = 'https://open.weixin.qq.com';

    /**
     * @Author: TinyMeng <666@majiameng.com>
     */
    public function run(){
        if (isset($_GET['code'])) {
            $state = isset($_GET['state']) ? $_GET['state'] : "";
            header('Location: ' . $_COOKIE['redirect_uri'] . '?code=' . $_GET['code'] . '&state=' . $state);
        } else {
            if(!isset($_GET['appid']) || !isset($_GET['response_type']) || !isset($_GET['scope'])){
                echo "参数缺失";
                return;
            }
            $state = isset($_GET['state']) ? $_GET['state'] : "";

            $protocol = $this->is_HTTPS() ? 'https://' : 'http://';
            $params   = array(
                'appid'         => $_GET['appid'],
                'redirect_uri'  => $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['DOCUMENT_URI'],
                'response_type' => $_GET['response_type'],
                'scope'         => $_GET['scope'],
                'state'         => $state,
            );
            if($_GET['scope'] == 'snsapi_login'){
                //扫码登录
                $AuthorizeURL = $this->AuthorizeURL . '/connect/qrconnect';
            }else{
                $AuthorizeURL = $this->AuthorizeURL . '/connect/oauth2/authorize';
            }

            setcookie('redirect_uri', urldecode($_GET['redirect_uri']), $_SERVER['REQUEST_TIME'] + 60, '/');
            header('Location: ' . $AuthorizeURL . '?' . http_build_query($params) . '#wechat_redirect');
        }
    }

    /**
     * 是否https
     * @Author: TinyMeng <666@majiameng.com>
     * @return bool
     */
    protected function is_HTTPS(){
        if (!isset($_SERVER['HTTPS'])) {
            return false;
        }
        if ($_SERVER['HTTPS'] === 1) { //Apache
            return true;
        } elseif ($_SERVER['HTTPS'] === 'on') { //IIS
            return true;
        } elseif ($_SERVER['SERVER_PORT'] == 443) { //其他
            return true;
        }
        return false;
    }
}

$app = new WxProxy();
$app->run();