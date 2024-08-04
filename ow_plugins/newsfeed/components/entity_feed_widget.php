<?php
/**
 * Feed Widget
 *
 * @package ow_plugins.newsfeed.components
 * @since 1.0
 */
class NEWSFEED_CMP_EntityFeedWidget extends NEWSFEED_CMP_FeedWidget
{
    private $feedId;
    private $feedType;

    protected $defaultParams = array(
        'statusForm' => true,
        'statusMessage' => null,
        'widget' => array()
    );

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct($paramObj);

        $this->feedId = $paramObj->additionalParamList['entityId'];
        $this->feedType = $paramObj->additionalParamList['entity'];
        $group = null;
        if (isset($paramObj->additionalParamList['group'])) {
            $group = $paramObj->additionalParamList['group'];
        }

        $additionalInfo = array();
        if (isset($paramObj->additionalParamList)) {
            $additionalInfo = $paramObj->additionalParamList;
        }
        $event = new OW_Event('feed.on_widget_construct', array(
            'feedId' => $this->feedId,
            'feedType' => $this->feedType,
            'group' => $group,
            'additionalInfo' => $additionalInfo,
        ));
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        $data = array_merge($this->defaultParams, $data);

        foreach ( $data['widget'] as $setting => $value )
        {
            $this->setSettingValue($setting, $value);
        }

        $feed = $this->createFeed($this->feedType, $this->feedId);
        $feed->setDisplayType(NEWSFEED_CMP_Feed::DISPLAY_TYPE_ACTIVITY);

        $isChannel = false;
        if (isset($paramObj->additionalParamList['isChannel'])) {
            $isChannel = $paramObj->additionalParamList['isChannel'];
        } else {
            $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.add.widget', array('feedId'=>$this->feedId, 'feedType'=>$this->feedType, 'group' => $group) ));
            $isChannelParticipant = $channelEvent->getData()['channelParticipant'];
            if(isset($isChannelParticipant) && $isChannelParticipant ){
                $isChannel = true;
            }
        }
        $isManager = false;
        if (isset($additionalInfo['currentUserIsManager'])) {
            $isManager = $additionalInfo['currentUserIsManager'];
        }
        if ((($group != null && $group->userId == OW::getUser()->getId()) || $isManager) && $isChannel) {
            $isChannel = false;
        }
        if ( $data['statusForm'] && !$isChannel)
        {
            $visibility = NEWSFEED_BOL_Service::VISIBILITY_FULL - NEWSFEED_BOL_Service::VISIBILITY_SITE;
            $userId = OW::getUser()->getId();
            OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FEED_RENDERED, array('userId' => $userId)));
            $feed->addStatusForm($this->feedType, $this->feedId, $visibility);
        } 
        else if (!empty($data['statusMessage'])) 
        {
            $feed->addStatusMessage($data['statusMessage']);
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
        $driver = OW::getClassInstance("NEWSFEED_CLASS_FeedDriver");
        
        return OW::getClassInstance("NEWSFEED_CMP_Feed", $driver, $feedType, $feedId);
    }
}