<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class FRMBOURSESMS_BOL_Service
{
    private static $classInstance;

    private $apikey;
    private $sender;
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
        $this->apikey = OW::getConfig()->getValue('frmboursesms','apikey');
        $this->sender = OW::getConfig()->getValue('frmboursesms','sender');
        $this->url = OW::getConfig()->getValue('frmboursesms','url');
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

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "message=".$text."&sender=".$this->sender."&Receptor=".$mobile,
            CURLOPT_HTTPHEADER => array(
                "apikey:".$this->apikey,
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            $data['error'] = true;
            $data['errorMessage'] = $err;
        }
        $data['result'] = $response;
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
