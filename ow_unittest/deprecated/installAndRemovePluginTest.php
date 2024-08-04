<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 7/11/2017
 * Time: 3:53 PM
 */
class installAndRemovePluginTest extends FRMUnitTestUtilites
{
    private static $PLUGIN_KEY = 'frmajaxloader';
    private $isPluginActivated;

    protected function setUp()
    {
        parent::setUp();
        $plugin = BOL_PluginDao::getInstance()->findPluginByKey(self::$PLUGIN_KEY);
        if (isset($plugin)) {
            $this->isPluginActivated = $plugin->isActive();
            if (!$this->isPluginActivated) {
                BOL_PluginService::getInstance()->activate(self::$PLUGIN_KEY);
            }
        }else{
            self::markTestSkipped('PLUGIN_NOT_INSTALLED');
        }
    }


    public function test()
    {
        //uninstall the plugin
        BOL_PluginService::getInstance()->uninstall(self::$PLUGIN_KEY);
        //plugin should not exists
        $activePlugins = BOL_PluginService::getInstance()->findActivePlugins();
        self::assertFalse(FRMSecurityProvider::existPluginKeyInActivePlugins($activePlugins, self::$PLUGIN_KEY));

        //install the plugin
        BOL_PluginService::getInstance()->install(self::$PLUGIN_KEY);
        //plugin should exists
        $activePlugins = BOL_PluginService::getInstance()->findActivePlugins();
        self::assertTrue(FRMSecurityProvider::existPluginKeyInActivePlugins($activePlugins, self::$PLUGIN_KEY));
    }

    protected function tearDown()
    {
        parent::tearDown();
        if(isset($this->isPluginActivated) && !$this->isPluginActivated)
        {
            BOL_PluginService::getInstance()->deactivate(self::$PLUGIN_KEY);
        }
    }


}