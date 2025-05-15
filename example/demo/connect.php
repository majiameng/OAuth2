<?php
/**
 * Oauth2登录SDK
 **/
error_reporting(0);
session_start();
@header('Content-Type: text/html; charset=UTF-8');

// 引用vendor
require __DIR__.'/../../vendor/autoload.php';
require __DIR__.'/../oauth2.php';

// 配置文件
$oauthConfig = require '../config/params.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'qq';
$oauth = new oauth2($oauthConfig);

if (!empty($_GET['code'])) {
    $arr = $oauth->callback($type);
    if (isset($arr['code']) && $arr['code'] == 0) {
        /* 处理用户登录逻辑 */
        $_SESSION['user'] = $arr['userInfo'];
        exit("<script language='javascript'>window.location.href='./';</script>");

    } elseif (isset($arr['code'])) {
        exit('登录失败，返回错误原因：' . $arr['msg']);
    } else {
        exit('获取登录数据失败');
    }
} else {
    $arr = $oauth->login($type);
     if (isset($arr['code']) && $arr['code'] == 0) {
         exit("<script language='javascript'>window.location.href='{$arr['url']}';</script>");
     } elseif (isset($arr['code'])) {
         exit('登录接口返回：' . $arr['msg']);
     } else {
         exit('获取登录地址失败');
     }
}
