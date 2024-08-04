<?php

class A_UtilTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    /***
     * UTIL_String
     */
    public function testString()
    {
        $res = UTIL_String::startsWith('salam', 'sa');
        self::assertTrue($res);
    }

    /***
     * UTIL_HtmlTag
     */
    public function testHtmlTag()
    {
        $res = UTIL_HtmlTag::stripTags('<a href>salam</a>ali');
        self::assertEquals($res, 'salamali');
    }

}