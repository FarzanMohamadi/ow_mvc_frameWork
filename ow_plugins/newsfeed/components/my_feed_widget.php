<?php
/**
 * My Feed Widget
 *
 * @package ow_plugins.newsfeed.components
 * @since 1.0
 */
class NEWSFEED_CMP_MyFeedWidget extends NEWSFEED_CMP_FeedWidget
{

    private $userId;

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct($paramObj);

        $feed = $this->createFeed('my', OW::getUser()->getId());
        $feed->setDisplayType(NEWSFEED_CMP_Feed::DISPLAY_TYPE_ACTIVITY);
        
        if ( OW::getUser()->isAuthorized('newsfeed', 'allow_status_update') )
        {
            OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FEED_RENDERED, array('userId' =>OW::getUser()->getId() )));
            $otpEvent=OW_EventManager::getInstance()->trigger(new OW_Event('newsfeed.check.chat.form'));
            if( !isset($otpEvent->getData()['removeDashboardStatusForm']) || !$otpEvent->getData()['removeDashboardStatusForm']){
                $feed->addStatusForm('user', OW::getUser()->getId());
            }
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
        $driver = OW::getClassInstance("NEWSFEED_CLASS_UserDriver");
        
        return OW::getClassInstance("NEWSFEED_CMP_Feed", $driver, $feedType, $feedId);
    }
    
    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}