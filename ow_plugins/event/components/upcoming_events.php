<?php
/**
 * User console component class.
 *
 * @package ow.ow_plugins.event.components
 * @since 1.0
 */
class EVENT_CMP_UpcomingEvents extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramsObj )
    {
        parent::__construct();

        $params = $paramsObj->customParamList;
        $isPublic=true;
        $userId=null;
        if(OW::getUser()->isAuthenticated())
        {
            $userId=OW::getUser()->getId();
            $isPublic=false;
        }
        $eventService = EVENT_BOL_EventService::getInstance();
        //$events = $eventService->findPublicEvents(null, $params['events_count']);
        $events = $eventService->findUpComingEventsForUser($params['events_count'],$userId);
       // $count = $eventService->findPublicEventsCount();
        $count = $eventService->findUpComingEventsForUserCount($userId);

        if ( ( !OW::getUser()->isAuthenticated() || (!OW::getUser()->isAuthorized('event', 'add_event') && !OW::getUser()->isAuthorized('event')&& !OW::getUser()->isAdmin())) && $count == 0 )
        {
            $this->setVisible(false);
            return;
        }

        $this->assign('events', $eventService->getListingDataWithToolbar($events));
        $this->assign('no_content_message', OW::getLanguage()->text('event', 'no_index_events_label', array('url' => OW::getRouter()->urlForRoute('event.add'))));

        if ($eventService->findPublicEventsCount() > 0) {
            if (OW::getUser()->isAuthenticated() && (OW::getUser()->isAuthorized('event', 'add_event'))) {
                $toolbarArray['addEvent'] = array('href' => OW::getRouter()->urlForRoute('event.add'), 'label' => OW::getLanguage()->text('event', 'add_new_button_label'));
            }
            $toolbarArray['viewAll'] = array('href' => OW::getRouter()->urlForRoute('event.view_event_list', array('list' => 'latest')), 'label' => OW::getLanguage()->text('event', 'view_all_label'));
            $this->assign('toolbar', $toolbarArray);
        }

        //set JSON-LD
        foreach($events as $event){
            $eventService->addJSONLD($event);
        }
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
            self::SETTING_TITLE => OW::getLanguage()->text('event', 'up_events_widget_block_cap_label'),
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_ICON => self::ICON_CALENDAR
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}