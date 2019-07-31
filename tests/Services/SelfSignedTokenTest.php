<?php

namespace FreedomSex\Tests\Services;

use FreedomSex\Services\SelfSignedToken;
use PHPUnit\Framework\TestCase;

class SelfSignedTokenTest extends TestCase
{
    /**
     * @var SelfSignedToken
     */
    public $token;

    public function setUp()
    {
        $this->token = new SelfSignedToken(10, '12345');
    }

    public function testSigned()
    {
        $token = $this->token->create();
        $this->assertTrue($this->token->signed($token));
//        echo $token;
//        $this->expectOutputString('');
    }

    public function testGetId()
    {
        $token = new SelfSignedToken(10, '12345');
        $this->assertNull($token->getId());

        $value = '6e000eeabea27aa13a0476d656e5a15e.1560148598.70272e700a46b3040ee53f2b083e3875';
        $this->assertEquals('6e000eeabea27aa13a0476d656e5a15e', $token->getId($value));
    }

    public function testCreate()
    {
        $this->assertNotEmpty($this->token->create());
    }

    public function testValid()
    {
        $token = $this->token->create();
        $this->assertNotNull($this->token->valid($token));
    }

    public function testParse()
    {
        $token = $this->token->create();
        $this->assertCount(3, $this->token->parse($token));
    }

    public function testGenerate()
    {
        $this->assertRegExp('/^[a-f0-9]{32}$/', $this->token->generateId());
    }

    public function testSign()
    {
        $token = new SelfSignedToken(10, '12345');
        $id = '4c28afda06a25a53f2c1a8ddc491edd5';
        $value = 'a2aa40240f39a82fa54eb2b8294fb556';
        $this->assertEquals($value, $token->sign($id, '1560150024'));
    }

    public function testExpired()
    {
        $token = new SelfSignedToken(10, '12345');
        list($id, $expire) = $token->parse($token->create());
        $created = $token->created();
        $this->assertTrue($token->expired($created));

        $this->assertFalse($token->expired($expire - 1));
        $this->assertFalse($token->expired($expire - 5));
        $this->assertFalse($token->expired($expire - 9));

        $this->assertTrue($token->expired($expire - 10));
        $this->assertTrue($token->expired($expire - 15));
        $this->assertTrue($token->expired('1456789023'));
    }
}
