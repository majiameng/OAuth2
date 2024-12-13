<?php
use PHPUnit\Framework\TestCase;
use tinymeng\OAuth2\OAuth;
/**
 * IntelligentParseTest
 */
class OauthTest extends TestCase
{
    public function testAddress()
    {
        $name = 'wechat';
        $oauth = OAuth::$name([]);
    }

}