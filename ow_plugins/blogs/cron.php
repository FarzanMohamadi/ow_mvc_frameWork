<?php
class BLOGS_Cron extends OW_Cron
{
    const IMAGES_DELETE_LIMIT = 10;

    public function __construct()
    {
        parent::__construct();

        $this->addJob('imagesDeleteProcess', 1);
    }

    public function run()
    {

    }

    public function imagesDeleteProcess()
    {
        $config = OW::getConfig();

        // check if uninstall is in progress
        if ( !$config->getValue('blogs', 'uninstall_inprogress') )
        {
            return;
        }

        // check if cron queue is not busy
        if ( $config->getValue('blogs', 'uninstall_cron_busy') )
        {
            return;
        }

        $config->saveConfig('blogs', 'uninstall_cron_busy', 1,null, false);

        $mediaPanelService = BOL_MediaPanelService::getInstance();

        $mediaPanelService->deleteImages('blogs', self::IMAGES_DELETE_LIMIT);

        $config->saveConfig('blogs', 'uninstall_cron_busy', 0, null, false);

        if ( !$mediaPanelService->countGalleryImages('blogs') )
        {
            $config->saveConfig('blogs', 'uninstall_inprogress', 0, null, false);
            BOL_PluginService::getInstance()->uninstall('blogs');
        } else {
            // The job should be run again
            OW::getEventManager()->trigger(new OW_Event(PostService::EVENT_UNINSTALL_IN_PROGRESS));
        }
    }
}