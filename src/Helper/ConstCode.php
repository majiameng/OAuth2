<?php
/**
 * 公共常量
 */
namespace tinymeng\OAuth2\Helper;

use tinymeng\tools\Tool;

class ConstCode{

    /** 公共状态 */
    const STATUS_DELETE = 0;//删除
    const STATUS_NORMAL = 1;//正常

    /** 性别 */
    const GENDER = 0;//未知
    const GENDER_MAN = 1;//男
    const GENDER_WOMEN = 2;//女

    /** 登录类型 */
    const TYPE_QQ               = 1; //QQ
    const TYPE_WECHAT           = 2; //微信
    const TYPE_WECHAT_MOBILE    = 3; //微信mobile
    const TYPE_SINA             = 4; //sina新浪微博
    const TYPE_GITHUB           = 5; //GitHub
    const TYPE_ALIPAY           = 6; //AliPay
    const TYPE_FACEBOOK         = 7; //faceBook
    const TYPE_GOOGLE           = 8; //google
    const TYPE_TWITTER          = 9; //飞鸽
    const TYPE_LINE             = 10;//line
    const TYPE_NAVER             = 11;//naver

    const TYPE_QQ_APP           = 21; //qqAPP
    const TYPE_WECHAT_APP       = 22; //微信APP
    const TYPE_WECHAT_APPLETS   = 23; //微信小程序
    
    const TYPE_DOUYIN           = 31; //抖音
    const TYPE_TOUTIAO          = 32; //头条
    const TYPE_XIGUA            = 33; //西瓜

    /**
     * Description:  getTypeConst
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param int $channel 渠道：登录方式
     * @param bool $type 类型：app applets
     * @return int
     */
    static public function getTypeConst($channel,$type="")
    {
        switch ($channel){
            case self::TYPE_QQ:
                if($type == 'app'){
                    $typeConst = self::TYPE_QQ_APP;//qqApp
                }else{
                    $typeConst = $channel;
                }
                break;
            case self::TYPE_WECHAT:
                if($type == 'app'){
                    $typeConst = self::TYPE_WECHAT_APP;//微信App
                }else if($type == 'applets'){
                    $typeConst =  self::TYPE_WECHAT_APPLETS;//微信小程序
                }else if(Tool::isWeiXin()){
                    $typeConst =  self::TYPE_WECHAT_MOBILE;//微信mobile
                }else{
                    $typeConst = $channel;
                }
                break;
            default:
                $typeConst = $channel;
                break;
        }
        return $typeConst;
    }

}
