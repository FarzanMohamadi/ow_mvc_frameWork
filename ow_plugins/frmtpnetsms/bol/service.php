<?php
require_once dirname(__FILE__) . DS. 'nusoap.php';
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class FRMTPNETSMS_BOL_Service
{
    private static $classInstance;

    private $userId;
    private $password;
    private $originator;
    private $url;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    public function __construct()
    {
        $this->userId = OW::getConfig()->getValue('frmtpnetsms','user_id');
        $this->password = OW::getConfig()->getValue('frmtpnetsms','password');
        $this->originator = OW::getConfig()->getValue('frmtpnetsms','originator');
        $this->url = OW::getConfig()->getValue('frmtpnetsms','url');
    }


    /**
     * @param $mobileNumber
     * @param $message
     * @return string
     */
    public function getXmsRequest($mobileNumber,$message)
    {
        $mobileNumber = substr($mobileNumber, -10);
        $xmsRequest = '<xmsrequest>
                        <userid>'.$this->userId.'</userid>      
                        <password>'.$this->password.'</password>     
                        <action>smssend</action> 
                        <body>          
                            <type>oto</type>          
                            <recipient mobile="'.$mobileNumber.'"  originator="'.$this->originator.'">'.$message.'</recipient>
                         </body>  
                     </xmsrequest>';
        return $xmsRequest;
    }

    /**
     * @param OW_Event $event
     * @return null
     */
    public function sendSMS(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if (!isset($params['mobile']))
            return;
        if (!isset($params['text']))
            return;
        $mobile = $params['mobile'];
        $text = $params['text'];
        $client=new nusoap_client($this->url, 'wsdl');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        $param=array('requestData'=>  $this->getXmsRequest($mobile,$text));
        $result = $client->call('XmsRequest', $param);
        $data['result'] = $result;
        $event->setData($data);
    }

    public function SMSProviderSettingIsComplete(OW_Event $event)
    {
        $event->setData(array('is_complete' => true));
    }

    public function getCredit(OW_Event $event)
    {
        return ['ignoreCredit' => true];
    }
}
