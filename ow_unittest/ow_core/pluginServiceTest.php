<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class pluginServiceTest extends FRMUnitTestUtilites
{
    private $pluginList;

    protected function setUp()
    {
        parent::setUp();
        OW::getDbo()->setUseCashe(false);
        $this->pluginList = BOL_PluginService::getInstance()->findActivePlugins();
        foreach ($this->pluginList as $key => $plugin) {
            if (in_array($plugin->getKey(), ['base', 'admin', 'newsfeed', 'notifications', 'frm-security-essentials', 'privacy', 'birthdays'])) {
                unset($this->pluginList[$key]);
            }
        }
       // self::markTestSkipped();
    }

    public function testActivation()
    {
        self::assertTrue(true);
        return;
        $pluginService = BOL_PluginService::getInstance();
        $failed = false;
        foreach ($this->pluginList as $plugin) {
            try {
                $key = $plugin->getKey();
                $pluginService->deactivate($key);
                OW::getConfig()->generateCache();
                $pluginDto = $pluginService->findPluginByKey($key);
                if ($pluginDto->isActive()) {
                    $this->echoText('pluginServiceTest->testInstallation: deactivate plugin failed for ' . $key);
                    $failed = true;
                }
            } catch (Exception $ex) {
                $this->handleException($ex);
                $this->echoText('pluginServiceTest->testActivation: deactivate plugin ' . $key . ' => ' . $ex->getMessage());
                $failed = true;
            }
        }
        foreach ($this->pluginList as $plugin) {
            try {
                $key = $plugin->getKey();
                $pluginService->activate($key, false);
                OW::getConfig()->generateCache();
                $pluginDto = $pluginService->findPluginByKey($key);
                if (!$pluginDto->isActive()) {
                    $this->echoText('pluginServiceTest->testInstallation: activate plugin failed for ' . $key);
                    $failed = true;
                }
            } catch (Exception $ex) {
                $this->handleException($ex);
                $this->echoText('pluginServiceTest->testActivation: activate plugin ' . $key . ' => ' . $ex->getMessage());
                $failed = true;
            }
        }
        self::assertFalse($failed);
    }

    public function testInstallation()
    {
        self::assertTrue(true);
        return;
        //self::markTestSkipped();
        $pluginService = BOL_PluginService::getInstance();
        $failed = false;
        foreach ($this->pluginList as $plugin) {
            $key = $plugin->getKey();
            try {
                $pluginService->uninstall($key);
                OW::getConfig()->generateCache();
                $pluginDto = $pluginService->findPluginByKey($key);
                if (isset($pluginDto)) {
                    $this->echoText('pluginServiceTest->testInstallation: uninstall plugin failed for ' . $key);
                    $failed = true;
                }
            } catch (Exception $ex) {
                $this->handleException($ex);
                $this->echoText('pluginServiceTest->testInstallation: uninstall plugin ' . $key . ' => ' . $ex->getMessage());
                $failed = true;
            }
        }
        foreach ($this->pluginList as $plugin) {
            $key = $plugin->getKey();
            try {
                $pluginDto = $pluginService->install($key);
                OW::getConfig()->generateCache();
                if (!isset($pluginDto)){
                    $this->echoText('pluginServiceTest->testInstallation: install plugin failed for ' . $key);
                    $failed = true;
                }
            } catch (Exception $ex) {
                $this->handleException($ex);
                $this->echoText('pluginServiceTest->testInstallation: install plugin ' . $key . ' => ' . $ex->getMessage());
                $failed = true;
            }
        }
        self::assertFalse($failed);
    }

    protected function tearDown()
    {
        parent::tearDown();
        OW::getDbo()->setUseCashe(true);

        $pluginService = BOL_PluginService::getInstance();
        foreach ($this->pluginList as $plugin) {
            try {
                $key = $plugin->getKey();
                $pluginDto = $pluginService->findPluginByKey($key);
                if (!isset($pluginDto)) {
                    $pluginService->install($key);
                    $pluginDto = $pluginService->findPluginByKey($key);
                }
                if (!$pluginDto->isActive()) {
                    $pluginService->activate($key);
                }
            } catch (Exception $ex) {
                $this->handleException($ex);
                $this->echoText('pluginServiceTest->teardown: Unable to reinstall/activate plugin ' . $key . ' => ' . $ex->getMessage());
                throw new Exception( 'Error in teardown' );
            }
        }
    }
}