<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmuserlogin
 * @since 1.0
 */
class FRMUSERLOGIN_Cron extends OW_Cron
{
    public function __construct()
    {
        parent::__construct();

        $this->addJob('deleteUsersLoginDetails', 24*60);
    }
    
    public function run()
    {

    }
    
    public function deleteUsersLoginDetails()
    {
        FRMUSERLOGIN_BOL_Service::getInstance()->deleteLoginDetails();
        FRMUSERLOGIN_BOL_Service::getInstance()->deleteActiveLoginDetails();
    }
}
