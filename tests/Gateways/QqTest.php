<?php
namespace Tests\Gateways;

use Tests\TestCase;
use tinymeng\OAuth2\OAuth;
use tinymeng\OAuth2\Helper\ConstCode;

class QqTest extends TestCase
{
    protected $oauth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->oauth = OAuth::qq($this->getConfig('qq'));
    }

    public function testGetRedirectUrl()
    {
        $url = $this->oauth->getRedirectUrl();
        $this->assertContains('https://graph.qq.com/oauth2.0/authorize', $url);
    }
}