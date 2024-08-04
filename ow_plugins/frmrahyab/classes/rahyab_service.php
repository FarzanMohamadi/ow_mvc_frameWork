<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/26/2017
 * Time: 3:02 PM
 */
class FRMRAHYAB_CLASS_RahyabService extends FRMRAHYAB_CLASS_Provider
{
    /**
     * FRMRAHYAB_CLASS_RahyabService constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @var FRMRAHYAB_CLASS_RahyabSmsTools
     */
    protected $smsTools;
    private static $INSTANCE;

    public static function getInstance()
    {
        if (!isset(self::$INSTANCE)) {
            self::$INSTANCE = new self();
        }
        self::$INSTANCE->init();
        return self::$INSTANCE;
    }

    /**
     * @return bool
     */
    function checkSettingCompletion()
    {
        return !(empty($this->service->getPanelUsername()) || empty($this->service->getPanelPassword()) || empty($this->service->getPanelNumber()) || empty($this->service->getHost()) || empty($this->service->getPort()) || empty($this->service->getCompany()));
    }

    function sendSMS($username, $password, $from, $to, $text)
    {
        return $this->smsTools->send_sms($username, $password, $from, array($to), array($to => $text));
    }

    function getCredit($username, $password)
    {
        $credit = $this->smsTools->get_cash($username, $password);
        return $credit != 'no' ? (int)$credit : 0;
    }

    function init()
    {
        $this->smsTools = FRMRAHYAB_CLASS_RahyabSmsTools::getInstance();
    }

    function checkStatus($username, $password,$from, $smsId)
    {
        return $this->smsTools->get_delivery($username,$password,$from,$smsId);
    }
}