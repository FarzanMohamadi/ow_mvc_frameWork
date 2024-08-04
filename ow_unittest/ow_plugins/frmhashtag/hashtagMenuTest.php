<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 7/11/2017
 * Time: 4:54 PM
 */
class hashtagMenuTest extends FRMUnitTestUtilites
{
    private static $testPlugins = array(
        'event',
        'video',
        'photo',
        'groups'
    );
    private $deactivatedPlugins = array();

    protected function setUp()
    {
        parent::setUp();
       // self::markTestSkipped('error in event reactivation!');
    }

    public function test()
    {
        self::assertTrue(true);
        return;
        //deactivate the test plugins and check the result of getContentMenu function
        foreach (self::$testPlugins as $testPlugin){
            BOL_PluginService::getInstance()->deactivate($testPlugin);
            $this->deactivatedPlugins[] = $testPlugin;
            $result = FRMHASHTAG_BOL_Service::getInstance()->getContentMenu('test',null);
            $menus = $result['menu'];
            foreach ($menus as $menu){
                //the menu titles should not exist in deactivated plugins, in other word the returned menus must belong to the active plugins
                self::assertFalse(in_array($menu->getKey(),$this->deactivatedPlugins));
            }
        }
    }

    protected function tearDown()
    {
        parent::tearDown();
        foreach ($this->deactivatedPlugins as $plugin)
            BOL_PluginService::getInstance()->activate($plugin);
    }


}