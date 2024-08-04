<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class FRMICTSMS_BOL_Service
{
    private static $classInstance;
    const USERNAME='maher';
    const PASSWORD = 'Erdf@weYT1';
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

    public function getToken()
    {
        return base64_encode(self::USERNAME.':'.self::PASSWORD);
    }

       public function sendSMS(OW_Event $event)
    {
        $params = $event->getParams();
        if (!isset($params['mobile']))
            return null;
        if (!isset($params['text']))
            return null;
        $url ='http://172.16.11.137:8080/mict-pa/api/sms/send?text='.$params['text'].'&number='.$params['mobile'];
        $ch2 = curl_init($url);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$this->getToken()));
        $str = curl_exec($ch2);
    }
}
