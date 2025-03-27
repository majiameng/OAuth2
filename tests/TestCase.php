<?php
namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // 模拟请求环境
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit Test';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    protected function getConfig($platform)
    {
        return [
            'app_id'        => 'test_app_id',
            'app_secret'    => 'test_app_secret',
            'scope'         => 'test_scope',
            'callback'      => 'http://localhost/callback',
            'response_type' => 'code',
            'grant_type'    => 'authorization_code',
            'proxy_url'     => null,
        ];
    }
}