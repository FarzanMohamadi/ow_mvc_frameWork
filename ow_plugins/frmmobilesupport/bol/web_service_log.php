<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobilesupport.bol
 * @since 1.0
 */
class FRMMOBILESUPPORT_BOL_WebServiceLog
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function logEndUserCrash()
    {
        if(!OW::getUser()->isAuthenticated())
        {
            return array( 'valid' => false);
        }

        if(!isset($_POST['crashLog']))
        {
            return array( 'valid' => false, 'message' =>  'There is no log');
        }
        
        OW::getLogger()->writeLog(OW_Log::ERROR,'App crash', json_decode($_POST['crashLog'], true));
        return array('valid' => true, 'message' => 'Logs were recorded');
    }
}