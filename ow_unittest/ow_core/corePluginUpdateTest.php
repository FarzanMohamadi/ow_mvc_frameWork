<?php
class corePluginUpdateTest extends FRMTestUtilites
{
    private $TEST_ADMIN_USERNAME = 'admin_test';
    private $TEST_ADMIN_EMAIL = 'adminForTest@gmail.com';
    private $TEST_ADMIN_PASSWORD = 'admin_test_pass';
    private $TEST_ADMIN_NAME = 'admin';
    private $PLUGIN_KEY = 'birthdays';

    private $admin;
    private $coreBuild;
    private $pluginBuild;

    public function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array($this->PLUGIN_KEY));
        ensure_session_active();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_ADMIN_USERNAME, $this->TEST_ADMIN_EMAIL, $this->TEST_ADMIN_PASSWORD, '1990/3/5', '1', $accountType);
        $this->admin = BOL_UserService::getInstance()->findByUsername($this->TEST_ADMIN_USERNAME);
        BOL_QuestionService::getInstance()->saveQuestionsData(array('realname' => $this->TEST_ADMIN_NAME), $this->admin->id);
        BOL_AuthorizationService::getInstance()->addAdministrator($this->admin->id);
        $this->coreBuild = (int)OW::getConfig()->getValue('base', 'soft_build');
        OW::getConfig()->saveConfig('base', 'soft_build', ( $this->coreBuild-3));

        $plugin = BOL_PluginService::getInstance()->findPluginByKey($this->PLUGIN_KEY);
        $this->pluginBuild = $plugin->getBuild();
        $plugin->setBuild(($this->pluginBuild - 1));
        BOL_PluginService::getInstance()->savePlugin($plugin);
    }
    public function testCoreAndPluginUpdate()
    {
        $test_caption = "coreAndPluginUpdate";
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();

        $this->url(OW_URL_HOME);
        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId);

        $this->sign_in($this->admin->getUsername(), $this->TEST_ADMIN_PASSWORD, true, true, $sessionId);

        //check update
        $this->url(OW::getRouter()->urlForRoute('admin_default'));
        $language = OW::getLanguage();

        //core
        $newPlatformInfo = FRMSecurityProvider::checkCoreUpdate(OW::getDbo());

        $textCoreUpdate = $language->text("admin", "manage_plugins_core_update_request_box_cap_label");
        $updateCoreExist = $this->checkIfXPathExists('//*[contains(@class,"ow_ic_plugin")and contains(text(),"' . $textCoreUpdate . '")]');
        self::assertTrue($updateCoreExist);
        $this->byCssSelector('input.ow_positive[type="button"][onclick*="/storage/platform-update-manually/?back-uri="]')->click();

        OW::getConfig()->generateCache();
        self::assertTrue($newPlatformInfo!=null);
        if ($newPlatformInfo != null) {
            $currentXmlInfo = $newPlatformInfo['currentXmlInfo'];
            $newVersion = (int)$currentXmlInfo['build'];
            self::assertEquals($newVersion, (int)OW::getConfig()->getValue('base', 'soft_build'));
        }

        //plugin
        $this->waitUntilElementLoaded('byCssSelector', 'input.ow_positive[type="button"][onclick*="/ow_updates/index.php?plugin="]', 60000);
        $plugin = BOL_PluginService::getInstance()->findPluginByKey($this->PLUGIN_KEY);
        $oldBuild = $plugin->getBuild();

        $this->byCssSelector('input.ow_positive[type="button"][onclick*="/ow_updates/index.php?update_all="]')->click();
        $this->url(OW_URL_HOME . 'admin/users');
        try {
            $this->waitUntilElementLoaded('byClassName', 'ow_ic_user', 120000);
        } catch (Exception $ex) {
            if (getenv("SNAPSHOT_DIR"))
                OW::getStorage()->fileSetContent(getenv("SNAPSHOT_DIR") . $test_caption . '_ow_ic_user.png', $this->currentScreenshot());
        }

        BOL_PluginService::getInstance()->updatePluginListCache();
        $plugin = BOL_PluginService::getInstance()->findPluginByKey($this->PLUGIN_KEY);

//        fwrite(STDERR, 'new ' . $plugin->getBuild());
//        fwrite(STDERR, ', old ' . $oldBuild);

        self::assertTrue($plugin->getBuild() > $oldBuild);
    }
    public function tearDown()
    {
        if($this->isSkipped)
            return;

        BOL_AuthorizationService::getInstance()->deleteModerator($this->admin->getId());
        FRMSecurityProvider::deleteUser($this->admin->getUsername());
        $plugin = BOL_PluginService::getInstance()->findPluginByKey($this->PLUGIN_KEY);
        $plugin->setBuild(($this->pluginBuild));
        BOL_PluginService::getInstance()->savePlugin($plugin);

        OW::getConfig()->saveConfig('base', 'soft_build', $this->coreBuild);
        parent::tearDown();
    }
}