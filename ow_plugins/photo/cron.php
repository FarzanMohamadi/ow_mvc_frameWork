<?php
/**
 * Photo cron job.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.photo
 * @since 1.0
 */
class PHOTO_Cron extends OW_Cron
{
    const ALBUMS_DELETE_LIMIT = 10;
    
    public function __construct()
    {
        parent::__construct();

        $this->addJob('albumsDeleteProcess');
        $this->addJob('contentIndexing');
        $this->addJob('cleareCache', 10);
        $this->addJob('deleteLimitedPhotos', 180);
    }

    public function run()
    {
        
    }

    public function albumsDeleteProcess()
    {
        $config = OW::getConfig();
        
        // check if uninstall is in progress
        if ( !$config->getValue('photo', 'uninstall_inprogress') )
        {
            return;
        }
        
        // check if cron queue is not busy
        if ( $config->getValue('photo', 'uninstall_cron_busy') )
        {
            return;
        }
        
        $config->saveConfig('photo', 'uninstall_cron_busy', 1);
        
        $albumService = PHOTO_BOL_PhotoAlbumService::getInstance();
        
        try
        {
            $albumService->deleteAlbums(self::ALBUMS_DELETE_LIMIT);
        }
        catch ( Exception $e )
        {
            OW::getLogger()->addEntry(json_encode($e));
        }

        $config->saveConfig('photo', 'uninstall_cron_busy', 0);
        
        if ( !$albumService->countAlbums() ) 
        {
            BOL_PluginService::getInstance()->uninstall('photo');
            $config->saveConfig('photo', 'uninstall_inprogress', 0);

            PHOTO_BOL_PhotoService::getInstance()->setMaintenanceMode(false);
        } else {
            OW::getEventManager()->trigger(new OW_Event(PHOTO_BOL_PhotoService::EVENT_UNINSTALL_IN_PROGRESS));
        }
    }
    
    public function cleareCache()
    {
        PHOTO_BOL_PhotoCacheDao::getInstance()->cleareCache();
    }
    
    public function deleteLimitedPhotos()
    {
        PHOTO_BOL_PhotoTemporaryService::getInstance()->deleteLimitedPhotos();
    }

    public function contentIndexing()
    {
        PHOTO_BOL_SearchService::getInstance()->contentIndexing();
    }
}
