<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmdatabackup
 * @since 1.0
 */
class FRMDATABACKUP_Cron extends OW_Cron
{
    public function __construct()
    {
        parent::__construct();

        $this->addJob('deleteBackupData', 60*24); //Checking for removing backup data per day
    }
    
    public function run()
    {

    }
    
    public function deleteBackupData()
    {
        $deadline = OW::getConfig()->getValue('frmdatabackup','deadline');
        if($deadline!=5){
            $deadlinePerMonth = $deadline*6;
            $deadlinePerDay = $deadlinePerMonth*30;
            $deadlinePerHour = $deadlinePerDay*24;
            $deadlinePerMinute = $deadlinePerHour*60;
            $timestamp = $deadlinePerMinute*60;
            OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_DATA_BACKUP_DELETE, array('timestamp' => $timestamp)));
        }


    }
}
