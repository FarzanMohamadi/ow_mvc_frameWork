<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/27/2017
 * Time: 9:15 AM
 */
class FRMFARAPAYAMAK_BOL_Service
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

    public function getSoapUrl()
    {
        if (!OW::getConfig()->configExists('frmfarapayamak', 'provider_soap_url'))
            return null;
        return OW::getConfig()->getValue('frmfarapayamak', 'provider_soap_url');
    }

    public function getRestUrls()
    {
        if (!OW::getConfig()->configExists('frmfarapayamak', 'provider_rest_urls'))
            return null;
        return json_decode(OW::getConfig()->getValue('frmfarapayamak', 'provider_rest_urls'),true);
    }

    public function getPanelUsername()
    {
        if (!OW::getConfig()->configExists('frmfarapayamak', 'panel_username'))
            return null;
        return OW::getConfig()->getValue('frmfarapayamak', 'panel_username');
    }

    public function getPanelPassword()
    {
        if (!OW::getConfig()->configExists('frmfarapayamak', 'panel_password'))
            return null;
        return OW::getConfig()->getValue('frmfarapayamak', 'panel_password');
    }

    public function getPanelNumber()
    {
        if (!OW::getConfig()->configExists('frmfarapayamak', 'panel_number'))
            return null;
        return OW::getConfig()->getValue('frmfarapayamak', 'panel_number');
    }

    public function sendSMS(OW_Event $event)
    {
        $params = $event->getParams();
        if (!isset($params['mobile']))
            return null;
        if (!isset($params['text']))
            return null;
        $result = $this->getSMSProvider()->sendSMS(
            $this->getPanelUsername(),
            $this->getPanelPassword(),
            $this->getPanelNumber(),
            $params['mobile'],
            $params['text']
        );
        $this->addTrack($params['mobile'],$params['text'],$result['recId'],time());
        return array('result' => $result['result']);
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

    /**
     * @return FRMFARAPAYAMAK_CLASS_Provider
     */
    public function getSMSProvider()
    {
        $config = OW::getConfig();
        $value = $config->getValue('frmfarapayamak', 'provider');
        if (isset($value))
            switch (strtolower($value)) {
                case 'rest':
                    return FRMFARAPAYAMAK_CLASS_FarapayamakRest::getInstance();
                case 'soap':
                    return FRMFARAPAYAMAK_CLASS_FarapayamakSoap::getInstance();
            }
        return FRMFARAPAYAMAK_CLASS_FarapayamakNull::getInstance();
    }

    public function addTrack($mobile,$message,$smsId,$time){
        $track = new FRMFARAPAYAMAK_BOL_Track();
        $track->mobile = $mobile;
        $track->message = $message;
        $track->time = $time;
        $track->smsId = $smsId;
        FRMFARAPAYAMAK_BOL_TrackDao::getInstance()->save($track);
    }

    public function checkSMSStatus($smsId){
        $provider = $this->getSMSProvider();
        $result = $provider->checkStatus(
            $this->getPanelUsername(),
            $this->getPanelPassword(),
            $smsId
        );
        return $result;
    }

    public function getStatusString($status){
        $language = OW::getLanguage();
        switch ($status){
            case 0:
                return $language->text('frmfarapayamak','status_0');
            case 1:
                return $language->text('frmfarapayamak','status_1');
            case 2:
                return $language->text('frmfarapayamak','status_2');
            case 3:
                return $language->text('frmfarapayamak','status_3');
            case 5:
                return $language->text('frmfarapayamak','status_5');
            case 8:
                return $language->text('frmfarapayamak','status_8');
            case 16:
                return $language->text('frmfarapayamak','status_16');
            case 35:
                return $language->text('frmfarapayamak','status_35');
            case 100:
                return $language->text('frmfarapayamak','status_100');
            case 200:
                return $language->text('frmfarapayamak','status_200');
            case 300:
                return $language->text('frmfarapayamak','status_300');
            case 400:
                return $language->text('frmfarapayamak','status_400');
            case 500:
                return $language->text('frmfarapayamak','status_500');
            default:
                return $language->text('frmfarapayamak','status_other');
        }
    }
}
