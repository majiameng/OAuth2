<?php
/**
 * QQ互联  https://connect.qq.com/index.html
 * api接口文档
 *      http://wiki.connect.qq.com/开发攻略_server-side
 * 注:
 *      1.如果要获取unionid，先去申请：http://wiki.connect.qq.com/开发者反馈
*/
namespace tinymeng\OAuth2\Gateways;
use tinymeng\OAuth2\Connector\Gateway;
use tinymeng\OAuth2\Helper\ConstCode;

/**
 * Class Qq
 * @package tinymeng\OAuth2\Gateways
 * @Author: TinyMeng <666@majiameng.com>
 * @Created: 2018/11/9
 */
class Qq extends Gateway
{
    const API_BASE            = 'https://graph.qq.com/';
    protected $AuthorizeURL   = 'oauth2.0/authorize';
    protected $AccessTokenURL = 'oauth2.0/token';
    protected $UserInfoURL = 'user/get_user_info';

    /**
     * @param $config
     * @throws \Exception
     */
    public function __construct($config)
    {
        parent::__construct($config);
        $this->AuthorizeURL = static::API_BASE.$this->AuthorizeURL;
        $this->AccessTokenURL = static::API_BASE.$this->AccessTokenURL;
        $this->UserInfoURL = static::API_BASE.$this->UserInfoURL;
    }

    /**
     * Description:  得到跳转地址
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return string
     */
    public function getRedirectUrl()
    {
        //存储state
        $this->saveState();
        //登录参数
        $params = [
            'response_type' => $this->config['response_type'],
            'client_id'     => $this->config['app_id'],
            'redirect_uri'  => $this->config['callback'],
            'state'         => $this->config['state'],
            'scope'         => $this->config['scope'],
            'display'       => $this->display,
        ];
        return $this->AuthorizeURL . '?' . http_build_query($params);
    }

    /**
     * Description:  获取格式化后的用户信息
     * @return array
     * @throws \Exception
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    public function userInfo()
    {
        $result = $this->getUserInfo();

        $userInfo = [
            'open_id' => $this->openid(),
            'union_id'=> $this->token['unionid'] ?? '',
            'access_token'=> $this->token['access_token'] ?? '',
            'channel' => ConstCode::TYPE_QQ,
            'nickname'=> $result['nickname'],
            'gender'  => isset($result['gender']) ? $this->getGender($result['gender']) : ConstCode::GENDER,
            'avatar'  => $result['figureurl_qq_2'] ? $result['figureurl_qq_2'] : $result['figureurl_qq_1'],
            'birthday'=> date('Y-m-d',strtotime($result['year'])),
        ];
        return $userInfo;
    }

    /**
     * Description:  获取原始接口返回的用户信息
     * @return array
     * @throws \Exception
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     */
    public function getUserInfo()
    {
        /** 获取用户信息 */
        $params = [
            'openid'=>$this->openid(),
            'oauth_consumer_key'=>$this->config['app_id'],
            'access_token'=>$this->token['access_token'],
            'format'=>'json',
        ];
        $data = $this->get($this->UserInfoURL, $params);
        return json_decode($data, true);
    }

    /**
     * Description:  获取当前授权用户的openid标识
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return mixed
     * @throws \Exception
     */
    public function openid()
    {
        if($this->type == 'app'){//App登录
            if(!isset($_REQUEST['access_token'])){
                throw new \Exception("腾讯QQ,APP登录 需要传输access_token参数! ");
            }
            $this->token['access_token'] = $_REQUEST['access_token'];
        }else{
            /** 获取token */
            $this->getToken();
        }
        if (!isset($this->token['openid']) || !$this->token['openid']) {
            $userID                 = $this->getOpenID();
            $this->token['openid']  = $userID['openid'];
            $this->token['unionid'] = isset($userID['unionid']) ? $userID['unionid'] : '';
        }
        return $this->token['openid'];
    }

    /**
     * Description:  解析access_token方法请求后的返回值
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @param $token
     * @return mixed
     * @throws \Exception
     */
    protected function parseToken($token)
    {
        parse_str($token, $data);
        if (isset($data['access_token'])) {
            return $data;
        } else {
            throw new \Exception("获取腾讯QQ ACCESS_TOKEN 出错：" . $token);
        }
    }

    /**
     * Description:  通过接口获取openid
     * @author: JiaMeng <666@majiameng.com>
     * Updater:
     * @return mixed|string
     * @throws \Exception
     */
    private function getOpenID(){
        $query = [
            'access_token' => $this->token['access_token']
        ];
        /** 如果要获取unionid，先去申请：http://wiki.connect.qq.com/开发者反馈 */
        if (isset($this->config['is_unioid']) && $this->config['is_unioid'] === true) {
            $query['unionid'] = 1;
        }

        $data = $this->get(self::API_BASE . 'oauth2.0/me',$query);
        $data     = json_decode(trim(substr($data, 9), " );\n"), true);
        if (isset($data['openid'])) {
            return $data;
        } else {
            throw new \Exception("获取用户openid出错：" . $data['error_description']);
        }
    }

    /**
     * 格式化性别参数
     * M代表男性,F代表女性
     * @param $gender
     */
    public function getGender($gender){
        return $gender == '男' ? ConstCode::GENDER_MAN : ConstCode::GENDER_WOMEN;
    }


    /**
     * 解密小程序 qq.getUserInfo() 敏感数据.
     *
     * @param string $encryptedData
     * @param string $iv
     * @param string $sessionKey
     *
     * @return array
     */
    public function descryptData($encryptedData, $iv, $sessionKey)
    {
        if (24 != strlen($sessionKey))
        {
            throw new \InvalidArgumentException('sessionKey 格式错误');
        }
        if (24 != strlen($iv))
        {
            throw new \InvalidArgumentException('iv 格式错误');
        }
        $aesKey = base64_decode($sessionKey);
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result = openssl_decrypt($aesCipher, 'AES-128-CBC', $aesKey, 1, $aesIV);
        if (!$result)
        {
            throw new \InvalidArgumentException('解密失败');
        }
        $dataObj = json_decode($result, true);
        if (!$dataObj)
        {
            throw new \InvalidArgumentException('反序列化数据失败');
        }

        return $dataObj;
    }

}
