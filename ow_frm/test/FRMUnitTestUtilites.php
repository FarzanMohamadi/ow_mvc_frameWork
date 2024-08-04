<?php
/*
 * Class PHPUnit_Framework_TestCase has been renamed to \PHPUnit\Framework\TestCase in recent versions.
 * This if helps to be compatible with both versions.
 */
if (!class_exists('PHPUnit_Framework_TestCase')) {
    class PHPUnit_Framework_TestCase extends \PHPUnit\Framework\TestCase {

    }
}

/**
 * Created by PhpStorm.
 * User: ismail
 * Date: 2/24/18
 * Time: 10:53 AM
 */
class FRMUnitTestUtilites extends PHPUnit_Framework_TestCase{
    protected $isSkipped = false;
    private $configChanges = array();

    protected function setUp()
    {
        parent::setUp();
        @file_put_contents('testcasename', $this->getName());
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->reverseConfigChanges();

        @unlink('testcasename');
    }

    protected function signIn($userId,$propagate=true){
       OW::getUser()->login($userId, $propagate);
    }

    protected function signOut(){
        OW::getUser()->logout();
    }

    protected function echoText($text, $bounding_box = false, $title = "ISSA")
    {
        if ($bounding_box) {
            fwrite(STDERR, "\n-----------------------------$title------------------------------------\n");
            fwrite(STDERR, "$text\n");
            fwrite(STDERR, "---------------------------------------------------------------------\n");
        } else
            fwrite(STDERR, "\n==========$title====>$text\n");
    }

    protected function checkRequiredPlugins($requiredPlugins)
    {
        foreach ($requiredPlugins as $pluginKey) {
            $plugin = BOL_PluginService::getInstance()->findPluginByKey($pluginKey);
            if ($plugin == null || !$plugin->isActive()) {
                $this->echoText('"' . $pluginKey . '" plugin is not active.'
                    , false, 'Test skipped');
                $this->isSkipped = true;
                $this->markTestSkipped('PLUGIN_NOT_INSTALLED');
                return;
            }
        }
    }

    protected function removeConfig($plugin, $key)
    {
        if (OW::getConfig()->configExists($plugin, $key)) {
            $before = OW::getConfig()->getValue($plugin, $key);
            OW::getConfig()->deleteConfig($plugin, $key);
            $this->addConfigChange($plugin, $key, $before);
        }
    }

    protected function addConfig($plugin, $key, $value)
    {
        if (OW::getConfig()->configExists($plugin, $key)) {
            $before = OW::getConfig()->getValue($plugin, $key);
            OW::getConfig()->saveConfig($plugin, $key, $value);
            $this->addConfigChange($plugin, $key, $before);
        } else {
            OW::getConfig()->saveConfig($plugin, $key, $value);
            $this->addConfigChange($plugin, $key, null, false);
        }
    }

    private function addConfigChange($plugin, $key, $beforeValue, $existedBefore = true)
    {
        $change = array(
            array(
                'plugin' => $plugin,
                'key' => $key,
                'before' => $beforeValue,
                'existed_before' => $existedBefore
            )
        );
        $this->configChanges = array_merge($change, $this->configChanges);
    }

    private function reverseConfigChanges()
    {
        foreach ($this->configChanges as $change) {
            if (!$change['existed_before']) {
                OW::getConfig()->deleteConfig($change['plugin'], $change['key']);
            } else {
                OW::getConfig()->saveConfig($change['plugin'], $change['key'], $change['before']);
            }
        }
    }

    protected function handleException(Exception $ex,$tag='',$shouldFail=true,$screenShot = true){
        $text = $ex;
        $this->echoText($text,true,'Exception');
        if($shouldFail){
            self::assertTrue(false);
        }
    }
}