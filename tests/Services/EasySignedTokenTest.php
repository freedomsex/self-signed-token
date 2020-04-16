<?php

namespace FreedomSex\Tests\Services;

use FreedomSex\Services\EasySignedToken;
use PHPUnit\Framework\TestCase;

class EasySignedTokenTest extends TestCase
{
    /**
     * @var SelfSignedToken
     */
    public $token;

    public function setUp()
    {
        $this->token = new EasySignedToken(10, '12345');
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
        $token = new EasySignedToken(10, '12345');
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

    public function testTest()
    {
        $this->assertTrue($this->token->test('6e000eeabea27aa13a0476d656e5a15e.1560148598.70272e700a46b3040ee53f2b083e3875'));
        $this->assertFalse($this->token->test('6e000eeabea27aa13a0476d656e5a15e.1560148598.70272e700a46b3040_ee53f2b083e3875'));
        $this->assertFalse($this->token->test('6e000eeabea27aa13a0476d656e5a15e.1560148598.70272e700a46b3'));
        $this->assertFalse($this->token->test('6e000eeabea27aa13a0476d656e5a15e.1560148598'));
        $this->assertFalse($this->token->test('6e000eeabea27aa13a0476d656e5a15e'));
    }


//    /**
//     * @dataProvider tokenIdVariant
//     */
//    public function testParseId($token, $result)
//    {
//        $this->assertCount(3, $this->token->parse($token));
//    }
//
//    public function tokenIdVariant()
//    {
//        return [
//            ['6e000eeabea27aa13a0476d656e5a15e.1560148598.70272e700a46b3040ee53f2b083e3875', ],
//        ];
//    }


    public function testGenerate()
    {
        $this->assertRegExp('/^[a-f0-9]{32}$/', $this->token->generateId());
    }

    public function testSign()
    {
        $token = new EasySignedToken(10, '12345');
        $id = '4c28afda06a25a53f2c1a8ddc491edd5';
        $value = 'a2aa40240f39a82fa54eb2b8294fb556';
        $this->assertEquals($value, $token->sign($id, '1560150024'));
    }

    public function testExpired()
    {
        $token = new EasySignedToken(10, '12345');
        [, $time] = $token->parse($token->create());
        $created = $token->created();
        $this->assertFalse($token->expired($created));

        $this->assertFalse($token->expired($time - 1));
        $this->assertFalse($token->expired($time - 5));
        $this->assertFalse($token->expired($time - 9));

        $this->assertTrue($token->expired($time - 10));
        $this->assertTrue($token->expired($time - 15));
        $this->assertTrue($token->expired('1456789023'));
    }
}
