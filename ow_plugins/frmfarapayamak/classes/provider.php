<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/27/2017
 * Time: 9:15 AM
 */
abstract class FRMFARAPAYAMAK_CLASS_Provider
{


    /**
     * FRMFRAPAYAMAK_CLASS_Provider constructor.
     */
    public function __construct()
    {
        $this->service = FRMFARAPAYAMAK_BOL_Service::getInstance();
    }

    protected $service;

    abstract function checkSettingCompletion();
    abstract function sendSMS($username, $password, $from, $to, $text);
    abstract function getCredit($username, $password);
    abstract function checkStatus($username, $password,$smsId);
}