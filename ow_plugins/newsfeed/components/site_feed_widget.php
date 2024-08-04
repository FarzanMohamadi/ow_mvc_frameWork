<?php
/**
 * Site Feed Widget
 *
 * @package ow_plugins.newsfeed.components
 * @since 1.0
 */
class NEWSFEED_CMP_SiteFeedWidget extends NEWSFEED_CMP_FeedWidget
{
    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct($paramObj);

        $feed = $this->createFeed('site', null);
        $feed->setDisplayType(NEWSFEED_CMP_Feed::DISPLAY_TYPE_ACTIVITY);
        $enabled = OW::getConfig()->getValue('newsfeed', 'index_status_enabled');
        $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_UPDATE_STATUS_FORM_CREATE));
        $showUpdateStatusForm = true;
        if(isset($event->getData()['showUpdateStatusForm'])) {
            $showUpdateStatusForm = $event->getData()['showUpdateStatusForm'];
        }
        if ( $showUpdateStatusForm && $enabled && OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('newsfeed', 'allow_status_update') )
        {
            $feed->addStatusForm('user', OW::getUser()->getId());
        }
        
        $this->setFeed( $feed );
    }
    
    /**
     * 
     * @param string $feedType
     * @param int $feedId
     * @return NEWSFEED_CMP_Feed
     */
    protected function createFeed( $feedType, $feedId )
    {
        $driver = OW::getClassInstance("NEWSFEED_CLASS_SiteDriver");
        
        return OW::getClassInstance("NEWSFEED_CMP_Feed", $driver, $feedType, $feedId);
    }
}