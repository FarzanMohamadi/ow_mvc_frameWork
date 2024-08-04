<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 7/12/2017
 * Time: 11:00 AM
 */
class isForceChangableTest extends FRMUnitTestUtilites
{
    private $backupConfigValue;
    protected function setUp()
    {
        parent::setUp();
        $this->backupConfigValue = OW::getConfig()->getValue('frmpasswordchangeinterval', 'dealWithExpiredPassword');
    }

    public function test()
    {
        OW::getConfig()->saveConfig('frmpasswordchangeinterval', 'dealWithExpiredPassword',FRMPASSWORDCHANGEINTERVAL_BOL_Service::DEAL_WITH_EXPIRED_PASSWORD_NORMAL_WITHOUT_NOTIF);
        //it should not change by force because dealWithExpiredPassword is normal
        self::assertFalse(FRMPASSWORDCHANGEINTERVAL_BOL_Service::getInstance()->isForceChangable());

        OW::getConfig()->saveConfig('frmpasswordchangeinterval', 'dealWithExpiredPassword',FRMPASSWORDCHANGEINTERVAL_BOL_Service::DEAL_WITH_EXPIRED_PASSWORD_NORMAL_WITH_NOTIF);
        //it should not change by force because dealWithExpiredPassword is normal_notif
        self::assertFalse(FRMPASSWORDCHANGEINTERVAL_BOL_Service::getInstance()->isForceChangable());

        OW::getConfig()->saveConfig('frmpasswordchangeinterval', 'dealWithExpiredPassword',FRMPASSWORDCHANGEINTERVAL_BOL_Service::DEAL_WITH_EXPIRED_PASSWORD_FORCE_WITH_NOTIF);
        //it should not change by force because dealWithExpiredPassword is force_notif
        self::assertTrue(FRMPASSWORDCHANGEINTERVAL_BOL_Service::getInstance()->isForceChangable());

        OW::getConfig()->saveConfig('frmpasswordchangeinterval', 'dealWithExpiredPassword','none');
        //it should not change by force because dealWithExpiredPassword is invalid
        self::assertFalse(FRMPASSWORDCHANGEINTERVAL_BOL_Service::getInstance()->isForceChangable());
    }


    protected function tearDown()
    {
        parent::tearDown();
        OW::getConfig()->saveConfig('frmpasswordchangeinterval', 'dealWithExpiredPassword',$this->backupConfigValue);
    }


}