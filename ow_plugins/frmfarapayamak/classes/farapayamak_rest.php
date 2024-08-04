<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/26/2017
 * Time: 3:02 PM
 */
class FRMFARAPAYAMAK_CLASS_FarapayamakRest extends FRMFARAPAYAMAK_CLASS_Provider
{
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
        return !(empty($this->service->getPanelUsername()) || empty($this->service->getPanelPassword()) || empty($this->service->getPanelNumber()) || empty($this->service->getRestUrls()));
    }

    function sendSMS($username, $password, $from, $to, $text)
    {
        $curl_post_data = array(
            'UserName' => $username,
            'PassWord' => $password,
            'To' => $to,
            'From' => $from,
            'Text' => $text,
            'IsFlash' => true
        );
        $curl_response = $this->run_command($this->service->getRestUrls()['send_sms'],$curl_post_data);
        if ($curl_response === false) {
            return array();
        }
        $decoded = json_decode($curl_response,true);
        return array(
            'result'=>$decoded['RetStatus'],
            'recId'=>$decoded['Value']
        );
    }

    function getCredit($username, $password)
    {
        $curl_post_data = array(
            'UserName' => $username,
            'PassWord' => $password
        );
        $curl_response = $this->run_command($this->service->getRestUrls()['get_credit'],$curl_post_data);
        if ($curl_response === false) {
            return 0;
        }
        $decoded = json_decode($curl_response,true);
        return $decoded['Value'] == 'Null' ? 0 : (int)$decoded['Value'];
    }

    function run_command($url, $data)
    {
        $curl = curl_init();
        @curl_setopt($curl, CURLOPT_URL, $url);
        @curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($curl, CURLOPT_POST, true);
        @curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        @curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        @curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $headers = array(
            'Content-Type: application/json',
        );
        @curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $curl_response = curl_exec($curl);
        @curl_close($curl);
        return $curl_response;
    }

    function checkStatus($username, $password, $smsId)
    {
        $curl_post_data = array(
            'UserName' => $username,
            'PassWord' => $password,
            'recID'=>$smsId
        );
        $curl_response = $this->run_command($this->service->getRestUrls()['check_status'],$curl_post_data);
        if ($curl_response === false) {
            return 0;
        }
        $decoded = json_decode($curl_response,true);
        return $decoded['Value'] == 'Null' ? 0 : (int)$decoded['Value'];
    }
}