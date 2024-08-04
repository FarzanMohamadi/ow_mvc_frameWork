<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/27/2017
 * Time: 9:15 AM
 */
class FRMRAHYAB_BOL_Service
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    public function __construct()
    {
    }

    public function getPanelUsername()
    {
        if (!OW::getConfig()->configExists('frmrahyab', 'panel_username'))
            return null;
        return OW::getConfig()->getValue('frmrahyab', 'panel_username');
    }

    public function getPanelPassword()
    {
        if (!OW::getConfig()->configExists('frmrahyab', 'panel_password'))
            return null;
        return OW::getConfig()->getValue('frmrahyab', 'panel_password');
    }

    public function getPanelNumber()
    {
        if (!OW::getConfig()->configExists('frmrahyab', 'panel_number'))
            return null;
        return OW::getConfig()->getValue('frmrahyab', 'panel_number');
    }

    public function getCompany()
    {
        if (!OW::getConfig()->configExists('frmrahyab', 'company'))
            return null;
        return OW::getConfig()->getValue('frmrahyab', 'company');
    }

    public function getHost()
    {
        if (!OW::getConfig()->configExists('frmrahyab', 'host'))
            return null;
        return OW::getConfig()->getValue('frmrahyab', 'host');
    }

    public function getPort()
    {
        if (!OW::getConfig()->configExists('frmrahyab', 'port'))
            return null;
        return OW::getConfig()->getValue('frmrahyab', 'port');
    }

    public function sendSMS(OW_Event $event)
    {
        $params = $event->getParams();
        if (!isset($params['mobile']))
            return null;
        if (!isset($params['text']))
            return null;
        $params['mobile'] = '0'.substr($params['mobile'], -10);
        $result = $this->getSMSProvider()->sendSMS(
            $this->getPanelUsername(),
            $this->getPanelPassword(),
            $this->getPanelNumber(),
            $params['mobile'],
            $params['text']
        );
        $this->addTrack($params['mobile'],$params['text'],$result[$params['mobile']],time());
        return array('result' => $result);
    }

    public function getCredit()
    {
        $result = $this->getSMSProvider()->getCredit(
            $this->getPanelUsername(),
            $this->getPanelPassword()
        );
        return array('credit' => (int)$result);
    }

    public function SMSProviderSettingIsComplete(OW_Event $event)
    {
        $result = $this->getSMSProvider()->checkSettingCompletion();
        $event->setData(array('is_complete' => $result));
    }

    public function SMSProviderSettingIsCompleteForTest()
    {
        $result = $this->getSMSProvider()->checkSettingCompletion();
        return array('is_complete' => $result);
    }

    /**
     * @return FRMRAHYAB_CLASS_Provider
     */
    public function getSMSProvider()
    {
        return FRMRAHYAB_CLASS_RahyabService::getInstance();
    }

    public function addTrack($mobile,$message,$smsId,$time){
        $track = new FRMRAHYAB_BOL_Track();
        $track->mobile = $mobile;
        $track->message = $message;
        $track->time = $time;
        $track->smsId = $smsId;
        FRMRAHYAB_BOL_TrackDao::getInstance()->save($track);
    }

    public function checkSMSStatus($smsId){
        $provider = $this->getSMSProvider();
        $result = $provider->checkStatus(
            $this->getPanelUsername(),
            $this->getPanelPassword(),
            $this->getPanelNumber(),
            $smsId
        );
        return $result;
    }

    public function getStatusString($status){
        $language = OW::getLanguage();
        switch ($status){
            case 0:
                return $language->text('frmrahyab','status_0');
            case 1:
                return $language->text('frmrahyab','status_1');
            case 2:
                return $language->text('frmrahyab','status_2');
            case 3:
                return $language->text('frmrahyab','status_3');
            case 4:
                return $language->text('frmrahyab','status_4');
            default:
                return $language->text('frmrahyab','status_0');
        }
    }
}
