<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/26/2017
 * Time: 2:44 PM
 */
class FRMSMS_CLASS_SmsProvider
{
    /**
     * FRMSMS_CLASS_SmsProvider constructor.
     */
    private function __construct()
    {
        $this->language = OW::getLanguage();
        $this->service = FRMSMS_BOL_Service::getInstance();
    }

    protected $language;
    protected $service;
    private static $INSTANCE;

    public static function getInstance()
    {
        if (!isset(self::$INSTANCE)) {
            self::$INSTANCE = new self();
        }
        return self::$INSTANCE;
    }

    /**
     * @return bool
     */
    public function notifyAdminIfSettingIsComplete()
    {
        $eventData = OW_EventManager::getInstance()->trigger(new OW_Event('frmsms.sms_provider_setting_is_complete'));
        if (!isset($eventData->getData()['is_complete'])) {
            $this->notifyAdminSMSProviderPluginNotFound();
            return false;
        }
        if (!$eventData->getData()['is_complete']) {
            $subject = $this->language->text('frmsms', 'admin_settings_title');
            $message = $this->language->text('frmsms', 'setting_is_empty');
            $this->service->sendMailToSiteEmail($subject, $message);
            return false;
        }
        return true;
    }

    public function notifyAdminCreditIsLowerThan($threshold)
    {
        $data = OW_EventManager::getInstance()->call('frmsms.get_credit');
        if(isset($data) && isset($data['ignoreCredit']))
        {
            return true;
        }
        if (!isset($data) || !isset($data['credit'])) {
            $this->notifyAdminSMSProviderPluginNotFound();
            return false;
        }
        if ($data['credit'] < $threshold) {
            //no credit or bellow threshold
            $subject = $this->language->text('frmsms', 'admin_settings_title');
            $message = $this->language->text('frmsms', 'no_credit');
            $this->service->sendMailToSiteEmail($subject, $message);
        }
        return true;
    }

    public function handleException(Exception $ex)
    {
        $subject = $this->language->text('frmsms', 'admin_settings_title');
        $message = $ex->getMessage();
        $this->service->sendMailToSiteEmail($subject, $message . $ex->getMessage());
    }

    public function notifyAdminSMSProviderPluginNotFound()
    {
        $subject = $this->language->text('frmsms', 'admin_settings_title');
        $message = $this->language->text('frmsms', 'no_provider_plugin_found');
        $this->service->sendMailToSiteEmail($subject, $message);
    }

    /**
     * @param string $mobile
     * @param string $text
     * @return bool
     */
    public function send($mobile, $text)
    {
        $ictPluginIsActive =false;
        if(FRMSecurityProvider::checkPluginActive('frmictsms', true)) {
            $ictPluginIsActive=true;
        }
        try {
            if (!$this->notifyAdminIfSettingIsComplete() && !$ictPluginIsActive ) {
                return false;
            }
            $eventData = OW_EventManager::getInstance()->trigger(new OW_Event('frmsms.send_sms', array('mobile' => $mobile, 'text' => $text)));
            if (!isset($eventData->getData()['result'])) {
                $this->notifyAdminSMSProviderPluginNotFound();
                return false;
            }
            $this->notifyAdminCreditIsLowerThan($this->service->getPanelThreshold());
            return $eventData->getData()['result'];
        } catch (Exception $e) {
            $this->handleException($e);
            return false;
        }
    }
}
