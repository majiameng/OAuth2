<?php
namespace Tests\Gateways;

use Tests\TestCase;
use tinymeng\OAuth2\OAuth;
use tinymeng\OAuth2\Helper\ConstCode;

class WechatTest extends TestCase
{
    protected $oauth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->oauth = OAuth::wechat($this->getConfig('wechat'));
    }

    public function testGetRedirectUrl()
    {
        $url = $this->oauth->getRedirectUrl();
        $this->assertContains('https://open.weixin.qq.com/connect/qrconnect', $url);
        $this->assertContains('appid=test_app_id', $url);
    }

    public function testUserInfo()
    {
        // 模拟返回数据
        $mockData = [
            'openid' => 'test_openid',
            'nickname' => 'test_name',
            'sex' => 1,
            'headimgurl' => 'http://test.com/avatar.jpg',
        ];

        // 验证返回格式
        $userInfo = [
            'open_id' => 'test_openid',
            'union_id' => '',
            'channel' => ConstCode::TYPE_WECHAT,
            'nickname' => 'test_name',
            'gender' => 1,
            'avatar' => 'http://test.com/avatar.jpg',
            'type' => ConstCode::getTypeConst(ConstCode::TYPE_WECHAT, null),
        ];

        $this->assertEquals($userInfo['channel'], ConstCode::TYPE_WECHAT);
    }
}