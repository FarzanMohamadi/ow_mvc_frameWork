<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class FRMRAHYABPAYAMGOSTARANSMS_BOL_Service
{
    private static $classInstance;

    private $sender;
    private $username;
    private $password;
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
        $this->sender = OW::getConfig()->getValue('frmrahyabpayamgostaransms','sender');
        $this->username = OW::getConfig()->getValue('frmrahyabpayamgostaransms','username');
        $this->password = OW::getConfig()->getValue('frmrahyabpayamgostaransms','password');
        $this->url = OW::getConfig()->getValue('frmrahyabpayamgostaransms','url');
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


        $client = new nusoap_client($this->url,true);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        $parameters['username'] = $this->username;
        $parameters['password'] = $this->password;
        $parameters['from'] =  $this->sender;
        $parameters['to'] = $mobile;
        $parameters['text'] =$text;
        $parameters['isflash'] = false;
        $result = $client->call('SendSimpleSMS', $parameters);

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
