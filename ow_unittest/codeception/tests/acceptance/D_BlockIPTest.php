<?php

class D_BlockIPTest extends \Codeception\Test\Unit
{
    /**
     * @var AcceptanceTester | Helper\Acceptance
     */
    protected $tester;

    protected function _before()
    {
        $this->tester->loginAsAdmin();
        $this->tester->amOnPage('/frmblockingip/admin');
        $this->tester->fillField('tryCountBlock','3');
        $this->tester->fillField('expTime','1');
        $this->tester->click('ثبت');
        $this->tester->logout();
    }

    /***
     * Successful Login
     */
    public function testBlockLogin()
    {
        $user = $this->tester->registerUser();

        self::assertFalse($this->tester->login($user->username, '123'));
        self::assertFalse($this->tester->login($user->username, '123'));
        $this->tester->see('شما مسدود شده‌اید!');

        sleep(70);
        self::assertTrue($this->tester->login($user->username, $user->password));
        $this->tester->logout();
    }

}