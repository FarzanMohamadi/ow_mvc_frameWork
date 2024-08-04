<?php
/**
 * User console component class.
 *
 * @package ow.ow_plugins.event.components
 * @since 1.0
 */
class EVENT_CMP_ProfilePageWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramsObj )
    {
        parent::__construct();

        $params = $paramsObj->customParamList;
        $addParams = $paramsObj->additionalParamList;
        
        if ( empty($addParams['entityId']) || !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('event', 'view_event') )
        {
            $this->setVisible(false);
            return;
        }
        else
        {
            $userId = $addParams['entityId'];
        }

        $eventParams =  array(
                'action' => 'event_view_attend_events',
                'ownerId' => $userId,
                'viewerId' => OW::getUser()->getId()
            );
        
        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch( RedirectException $e )
        {
            $this->setVisible(false);
            return;
        }
        
        $language = OW::getLanguage();
        $eventService = EVENT_BOL_EventService::getInstance();

        if(OW::getUser()->isAuthenticated() && $userId == OW::getUser()->getId()){
            //$userEvents = $eventService->findUserEvents($userId, null, $params['events_count']);
            $isPublic=false;
            $userEvents = $eventService->findEventsForUser(1,  $params['events_count'],$userId, null, false, array(), false,$isPublic, null);
        }else{
            //$userEvents = $eventService->findUserParticipatedPublicEvents($userId, null, $params['events_count']);
            $isPublic=true;
            $userEvents = $eventService->findEventsForUser(1,  $params['events_count'],$userId, null, false, array(), false,$isPublic, null);
        }


        if ( empty($userEvents) )
        {
            $this->setVisible(false);
            return;
        }

        $this->assign('my_events', $eventService->getListingDataWithToolbar($userEvents));

        $toolbarArray = array();
        if(OW::getUser()->isAuthenticated() && $userId == OW::getUser()->getId()){
           // $count = $eventService->findUsersEventsCount($userId);
            $isPublic=false;
            $count = $eventService->findEventsForUserCount($userId, null, false, null, true, $isPublic, null);
        }else{
           // $count = $eventService->findUserParticipatedPublicEventsCount($userId);
            $isPublic=true;
            $count = $eventService->findEventsForUserCount($userId, null, false, null, true, $isPublic, null);
        }
        $toolbarArray[] = array('label' => OW::getLanguage()->text('event', 'total_events', array('total' => $count)));
        if ($count  > $params['events_count'] )
        {
            $url = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('event.view_event_list', array('list' => 'user-participated-events')), array('userId' => $userId));
            $toolbarArray[] = array('href' => $url, 'label' => $language->text('event', 'view_all_label'));
        }

        $this->assign('toolbars', $toolbarArray);
    }

    public static function getSettingList()
    {
        $eventConfigs = EVENT_BOL_EventService::getInstance()->getConfigs();
        $settingList = array();
        $settingList['events_count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('event', 'cmp_widget_events_count'),
            'optionList' => $eventConfigs[EVENT_BOL_EventService::CONF_WIDGET_EVENTS_COUNT_OPTION_LIST],
            'value' => $eventConfigs[EVENT_BOL_EventService::CONF_WIDGET_EVENTS_COUNT]
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('event', 'profile_events_widget_block_cap_label'),
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => self::ICON_CALENDAR
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}