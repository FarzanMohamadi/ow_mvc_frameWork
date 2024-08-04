<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 2/24/2018
 * Time: 9:20 AM
 */
class frmrahyabTest extends FRMUnitTestUtilites
{
    private static $SMS_ID_PREFIX = 'test_rahyab_';
    private static $PLUGIN_KEY = 'frmrahyab';
    /**
     * @var FRMRAHYAB_BOL_Service
     */
    private $rahyabService;
    /**
     * @var FRMRAHYAB_BOL_TrackDao
     */
    private $trackDao;
    private $configChanges;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('frmrahyab'));
        $this->rahyabService = FRMRAHYAB_BOL_Service::getInstance();
        $this->trackDao = FRMRAHYAB_BOL_TrackDao::getInstance();
        $this->configChanges = array();
    }

    public function testAddTrack()
    {
        $mobile = '0910000000';
        $message = 'test rahyab';
        $smsId = uniqid(self::$SMS_ID_PREFIX);
        $time = time();
        $this->rahyabService->addTrack($mobile, $message, $smsId, $time);

        $example = new OW_Example();
        $example->andFieldEqual('smsId', $smsId);
        $track = $this->trackDao->findObjectByExample($example);
        self::assertTrue(isset($track));
        self::assertEquals($track->smsId, $smsId);
        self::assertEquals($track->message, $message);
        self::assertEquals($track->mobile, $mobile);
        self::assertEquals($track->time, $time);
    }

    public function testSMSProviderSettingIsComplete()
    {
        $this->addConfig(self::$PLUGIN_KEY, 'panel_username', uniqid(self::$SMS_ID_PREFIX));
        $this->addConfig(self::$PLUGIN_KEY, 'panel_password', uniqid(self::$SMS_ID_PREFIX));
        $this->addConfig(self::$PLUGIN_KEY, 'panel_number', uniqid(self::$SMS_ID_PREFIX));
        $this->addConfig(self::$PLUGIN_KEY, 'company', uniqid(self::$SMS_ID_PREFIX));
        $this->addConfig(self::$PLUGIN_KEY, 'host', uniqid(self::$SMS_ID_PREFIX));
        $this->addConfig(self::$PLUGIN_KEY, 'port', uniqid(self::$SMS_ID_PREFIX));

        self::assertTrue($this->rahyabService->SMSProviderSettingIsCompleteForTest()['is_complete']);

        $this->removeConfig(self::$PLUGIN_KEY, 'host');

        self::assertFalse($this->rahyabService->SMSProviderSettingIsCompleteForTest()['is_complete']);
    }

    public function testGetStatusString()
    {
        $language = OW::getLanguage();
        $keyPrefix = 'status_';

        $status = $this->rahyabService->getStatusString(0);
        self::assertEquals($status, $language->text('frmrahyab', $keyPrefix . '0'));

        $status = $this->rahyabService->getStatusString(1);
        self::assertEquals($status, $language->text('frmrahyab', $keyPrefix . '1'));

        $status = $this->rahyabService->getStatusString(2);
        self::assertEquals($status, $language->text('frmrahyab', $keyPrefix . '2'));

        $status = $this->rahyabService->getStatusString(3);
        self::assertEquals($status, $language->text('frmrahyab', $keyPrefix . '3'));

        $status = $this->rahyabService->getStatusString(4);
        self::assertEquals($status, $language->text('frmrahyab', $keyPrefix . '4'));

        $status = $this->rahyabService->getStatusString(5);
        self::assertEquals($status, $language->text('frmrahyab', $keyPrefix . '0'));
    }

    private function deleteAll()
    {
        $example = new OW_Example();
        $example->andFieldLike('smsId', self::$SMS_ID_PREFIX . '%');
        $this->trackDao->deleteByExample($example);
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

    protected function tearDown()
    {
        parent::tearDown();
        $this->deleteAll();
        $this->reverseConfigChanges();
    }

}