<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/26/2017
 * Time: 3:02 PM
 */
class FRMFARAPAYAMAK_CLASS_FarapayamakNull extends FRMFARAPAYAMAK_CLASS_Provider
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
        return false;
    }

    function sendSMS($username, $password, $from, $to, $text)
    {
        return null;
    }

    function getCredit($username, $password)
    {
        return 0;
    }

    function checkStatus($username, $password, $smsId)
    {
        return 0;
    }
}