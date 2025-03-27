<?php
use PHPUnit\Framework\TestCase;
use tinymeng\OAuth2\OAuth;
use tinymeng\OAuth2\Exception\OAuthException;

class OAuthTest extends TestCase
{
    public function testInvalidPlatform()
    {
        $this->expectException(OAuthException::class);
        OAuth::invalid([]);
    }

    public function testMissingConfig()
    {
        $this->expectException(OAuthException::class);
        OAuth::wechat([]);
    }
}