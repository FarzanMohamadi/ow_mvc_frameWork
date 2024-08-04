<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/26/2017
 * Time: 3:02 PM
 */
class FRMFARAPAYAMAK_CLASS_FarapayamakSoap extends FRMFARAPAYAMAK_CLASS_Provider
{
    /**
     * FRMSMS_CLASS_FarapayamakSoap constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->client = new SoapClient($this->service->getSoapUrl());
    }

    protected $client;
    private static $INSTANCE;

    public static function getInstance()
    {
        if(!isset(self::$INSTANCE))
        {
            self::$INSTANCE = new self();
        }
        return self::$INSTANCE;
    }

    /**
     * @return bool
     */
    function checkSettingCompletion()
    {
        return !(empty($this->service->getPanelUsername()) || empty($this->service->getPanelPassword()) || empty($this->service->getPanelNumber()) || empty($this->service->getSoapUrl()));
    }

    function sendSMS($username, $password, $from, $to, $text)
    {
        ini_set("soap.wsdl_cache_enabled", "0");
        $encoding = 'UTF-8';
        $parameters['username'] = $username;
        $parameters['password'] = $password;
        $parameters['from'] = $from;
        $parameters['to'] = array($to);
        $parameters['text'] = iconv($encoding, 'UTF-8//TRANSLIT', $text);
        $parameters['isflash'] = true;
        $parameters['udh'] = "";
        $parameters['recId'] = array(0);
        $parameters['status'] = 0x0;
        $result = $this->client->SendSms($parameters);
        return array(
            'result'=>$result->SendSmsResult,
            'recId'=>$result->recId->long
        );
    }

    function getCredit($username, $password)
    {
        return (int) $this->client->GetCredit(array("username"=>$username,"password"=>$password))->GetCreditResult;
    }

    function checkStatus($username, $password, $smsId)
    {
        ini_set("soap.wsdl_cache_enabled", "0");
        $parameters['recId'] = $smsId.'';
        $result = $this->client->GetDelivery($parameters);
        return $result->GetDeliveryResult;
    }
}