<?php

class C_InstallPluginTest extends \Codeception\Test\Unit
{
    /**
     * @var \AcceptanceTester
     */
    protected $tester;

    /***
     * Successful Login
     */
    public function testInstallPlugins()
    {
        $pluginsToInstall = array(
            'frmmobilesupport',
            'frmblockingip',
            'frmmainpage',
            'frmvideoplus',
            'frmgroupsplus'
        );
        $this->tester->loginAsAdmin();

        $this->tester->amOnPage('/admin/plugins/available');
        $this->tester->see('افزونه‌های در دسترس');
        foreach ($pluginsToInstall as $plugin) {
            try {
                $this->tester->click("#install-{$plugin}");
            }catch (Exception $ex){
                print("Plugin {$plugin} is already installed!");
            }
        }
        $this->tester->logout();
    }

}