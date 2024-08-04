<?php

class A_InstallTest extends \Codeception\Test\Unit
{
    /**
     * @var AcceptanceTester | Helper\Acceptance
     */
    protected $tester;

    public function _after()
    {
        parent::_after();

        include_once OW_DIR_ROOT . 'ow_includes' . DS . 'config.php';
    }

    /***
     * Install 
     */
    public function testInstall()
    {
        if($this->tester->isSiteInstalled()){
            echo "\n>>>> WARNING: SITE IS ALREADY INSTALLED";
            return;
        }
        /**
         * rules
         */
        $this->tester->amOnPage('/');
        $this->tester->see('قوانین مرتبط را مطالعه کردم و به آن‌ها پای‌بندی دارم');
        $this->tester->checkOption('rules_accepted');
        $this->tester->click('ادامه');

        /**
         * site setting
         */
        $this->tester->see('اطلاعات سایت');
        $this->tester->fillField('site_title', $this->tester->getSiteInfo('site_title'));
        $this->tester->fillField('admin_email', $this->tester->getSiteInfo('site_admin_email'));
        $this->tester->fillField('admin_username', $this->tester->getSiteInfo('site_admin_username'));
        $this->tester->fillField('admin_password',  $this->tester->getSiteInfo('site_admin_password'));
        $this->tester->click('ادامه');

        /**
         *  DB
         */
        $this->tester->see('لطفا پایگاه داده را ایجاد کرده و اطلاعات آن را وارد نمایید.');
        $this->tester->fillField('db_host', $this->tester->getSiteInfo('db_host'));
        $this->tester->fillField('db_user', $this->tester->getSiteInfo('db_user'));
        $this->tester->fillField('db_name', $this->tester->getSiteInfo('db_name'));
        $this->tester->fillField('db_password', $this->tester->getSiteInfo('db_password'));
        $this->tester->fillField('db_prefix', $this->tester->getSiteInfo('db_prefix'));
        $this->tester->click('ادامه');


        /**
         * cron
         */
        $this->tester->click('continue');

        /**
         * plugins
         */
        $this->tester->checkOption('//input[@id="blogs"]');
        $this->tester->checkOption('//input[@id="groups"]');
        $this->tester->checkOption('//input[@id="event"]');
        $this->tester->click('پایان');


        /**
         * complete
         */
        $this->tester->see('فرایند نصب تمام شد.');
        $this->tester->click('//a[text()="صفحه اصلی"]');


        /**
         * fill required questions
         */
        $this->tester->see('سوالات مورد نیاز نمایه');
        $this->tester->selectOption('//input[@name="sex"]', '1');
        $yearValue = $this->tester->grabTextFrom('form select[name=year_birthdate] option:nth-child(20)');
        $this->tester->selectOption("form select[name=year_birthdate]", $yearValue);
        $monthValue = $this->tester->grabTextFrom('form select[name=month_birthdate] option:nth-child(3)');
        $this->tester->selectOption("form select[name=month_birthdate]", $monthValue);

        $dayValue = $this->tester->grabTextFrom("//select[@name='day_birthdate']/option[10]");
        $this->tester->selectOption('form select[name=day_birthdate]', $dayValue);

        $this->tester->fillField('birthdate',$yearValue.'/2/'.$dayValue);

        $this->tester->click('submit');

        $this->tester->dontSee('سوالات مورد نیاز نمایه');
        $this->tester->logout();

    }
}