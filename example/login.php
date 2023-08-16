<?php
/**
 * Oauth 登录事例
 */
@header('Content-Type: text/html; charset=UTF-8');
// 引用vendor
require __DIR__.'/../vendor/autoload.php';
require 'oauth2.php';

//引入配置文件
$params = require('config/params.php');

try{
    $name="qq";//登录类型,例:qq / google
    $oauth = new oauth2($params);
    if (empty($_GET['code'])) {
        /** 登录 */
        $result = $oauth->login($name);
//        var_dump($result['url']);die;
        //重定向到第三方登录页
        header('Location: ' . $result['url']);
    }else{
        /** 登录回调 */
        $result = $this->callback($name);
        var_dump($result);die;
    }
}catch(Exception $e){
    echo 'Oauth登录失败！'.$e->getMessage();
}