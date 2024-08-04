<?php

class B_CheckInstalledTest extends \Codeception\Test\Unit
{
    /**
     * @var AcceptanceTester | Helper\Acceptance
     */
    protected $tester;

    /***
     * Check Installed 
     */
    public function testCheckInstalled()
    {
        self::assertTrue($this->tester->isSiteInstalled());
        self::assertTrue(defined('OW_URL_HOME'));
        self::assertTrue(defined('OW_DIR_ROOT'));
    }
}