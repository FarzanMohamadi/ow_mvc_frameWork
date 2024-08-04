<?php
/**
 * frmvideoplus cron job.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package frmvideoplus
 * @since 1.0
 */
class FRMVIDEOPLUS_Cron extends OW_Cron
{
    const VIDEO_DELETE_LIMIT = 180;
    public function __construct()
    {
        parent::__construct();

        $this->addJob('videoFileDeleteProcess');
    }

    public function run()
    {
        
    }

    public function videoFileDeleteProcess()
    {
        $config = OW::getConfig();
        
        // check if uninstall is in progress
        if ( !$config->getValue('frmvideoplus', 'uninstall_inprogress') )
        {
            return;
        }
        
        // check if cron queue is not busy
        if ( $config->getValue('frmvideoplus', 'uninstall_cron_busy') )
        {
            return;
        }
        
        $config->saveConfig('frmvideoplus', 'uninstall_cron_busy', 1);
        
        $frmvideoplusService = FRMVIDEOPLUS_BOL_Service::getInstance();
        $allFileDeleted=false;
        try
        {
            $allFileDeleted=$frmvideoplusService->deleteAllVideoFiles(self::VIDEO_DELETE_LIMIT);
        }
        catch ( Exception $e )
        {
            OW::getLogger()->addEntry(json_encode($e));
        }

        $config->saveConfig('frmvideoplus', 'uninstall_cron_busy', 0);
        
        if ( $allFileDeleted )
        {
            $config->saveConfig('frmvideoplus', 'uninstall_inprogress', 0);
            FRMVIDEOPLUS_BOL_Service::getInstance()->setMaintenanceMode(false);
            BOL_PluginService::getInstance()->uninstall('frmvideoplus');
        } else {
            OW::getEventManager()->trigger(new OW_Event(FRMVIDEOPLUS_BOL_Service::EVENT_UNINSTALL_IN_PROGRESS));
        }
    }
}
