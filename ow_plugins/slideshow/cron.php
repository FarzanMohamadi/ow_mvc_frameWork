<?php
/**
 * Slideshow cron job
 * 
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.slideshow
 * @since 1.4.0
 */
class SLIDESHOW_Cron extends OW_Cron
{
    const SLIDE_DELETE_LIMIT = 1;
    
    public function __construct()
    {
        parent::__construct();

        $this->addJob('slideDeleteProcess', 30);
        
        $this->addJob('slideshowUninstallProcess', 1);
    }

    public function run() { }

    public function slideDeleteProcess()
    {
        $service = SLIDESHOW_BOL_Service::getInstance();
        
        $list = $service->getDeleteQueueList(self::SLIDE_DELETE_LIMIT);
    	
        if ( !$list )
        {
            return;
        }
        
        foreach ( $list as $slide )
        {
            $service->deleteSlideById($slide->id);
        }
        
        return true;
    }
    
    public function slideshowUninstallProcess()
    {
        $config = OW::getConfig();
        
        // check if uninstall is in progress
        if ( !$config->getValue('slideshow', 'uninstall_inprogress') )
        {
            return;
        }

        // check if cron queue is not busy
        if ( $config->getValue('slideshow', 'uninstall_cron_busy') )
        {
            return;
        }

        $config->saveConfig('slideshow', 'uninstall_cron_busy', 1);

        $service = SLIDESHOW_BOL_Service::getInstance();

        $list = $service->getDeleteQueueList(self::SLIDE_DELETE_LIMIT);

        if ( $list )
        {
	        foreach ( $list as $slide )
	        {
	            $service->deleteSlideById($slide->id);
	        }
	        
        	$config->saveConfig('slideshow', 'uninstall_cron_busy', 0);
            OW::getEventManager()->trigger(new OW_Event(SLIDESHOW_BOL_Service::EVENT_UNINSTALL_IN_PROGRESS));
        }
        else {
            $config->saveConfig('slideshow', 'uninstall_inprogress', 0);
            BOL_PluginService::getInstance()->uninstall('slideshow');
        }
    }
}