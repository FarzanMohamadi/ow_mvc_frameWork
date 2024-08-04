<?php
/**
 * User Feed Widget
 *
 * @package ow_plugins.newsfeed.components
 * @since 1.0
 */
class NEWSFEED_CMP_UserFeedWidget extends NEWSFEED_CMP_FeedWidget
{

    private $userId;

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct($paramObj);

        $userId = $paramObj->additionalParamList['entityId'];

        // privacy check
        $viewerId = OW::getUser()->getId();
        $ownerMode = $userId == $viewerId;
        $modPermissions = OW::getUser()->isAuthorized('newsfeed');

        if ( !$ownerMode && !$modPermissions )
        {
            $privacyParams = array('action' => NEWSFEED_BOL_Service::PRIVACY_ACTION_VIEW_MY_FEED, 'ownerId' => $userId, 'viewerId' => $viewerId);
            $event = new OW_Event('privacy_check_permission', $privacyParams);

            try {
                OW::getEventManager()->trigger($event);
            }
            catch ( RedirectException $e )
            {
                $this->setVisible(false);

                return;
            }
        }

        $feed = $this->createFeed('user', $userId);

        $isBloacked = BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $userId);
        
        if ( (OW::getUser()->isAuthorized('base', 'add_comment') &&
                OW::getUser()->isAuthorized('newsfeed', 'allow_status_update'))
            || OW::getUser()->isAdmin())
        {
            if ( $isBloacked )
            {
                $feed->addStatusMessage(OW::getLanguage()->text("base", "user_block_message"));
            }
            else
            {
                $visibility = NEWSFEED_BOL_Service::VISIBILITY_FULL;
                $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_UPDATE_STATUS_FORM_CREATE_IN_PROFILE, array('userId' => $userId)));
                $showUpdateStatusForm = true;
                if(isset($event->getData()['showUpdateStatusForm'])) {
                    $showUpdateStatusForm = $event->getData()['showUpdateStatusForm'];
                }
                if($showUpdateStatusForm) {
                    OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FEED_RENDERED, array('userId' =>$userId )));
                    $feed->addStatusForm('user', $userId, $visibility);
                }
            }
        } 
        else 
        {
            $actionStatus = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'add_comment');
            
            if ( $actionStatus["status"] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $feed->addStatusMessage($actionStatus["msg"]);
            }
        }

        $feed->setDisplayType(NEWSFEED_CMP_Feed::DISPLAY_TYPE_ACTIVITY);
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
        $driver = OW::getClassInstance("NEWSFEED_CLASS_FeedDriver");
        
        return OW::getClassInstance("NEWSFEED_CMP_Feed", $driver, $feedType, $feedId);
    }
}