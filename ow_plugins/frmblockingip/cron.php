<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmblockingip
 * @since 1.0
 */
class FRMBLOCKINGIP_Cron extends OW_Cron
{
    public function __construct()
    {
        parent::__construct();

        $this->addJob('deleteBlockIp', 1);
    }
    
    public function run()
    {

    }
    
    public function deleteBlockIp()
    {
        FRMBLOCKINGIP_BOL_Service::getInstance()->deleteBlockIp();
    }
}
