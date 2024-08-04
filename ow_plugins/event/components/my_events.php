<?php
/**
 * User console component class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.event.components
 * @since 1.0
 */
class EVENT_CMP_MyEvents extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramsObj )
    {
        parent::__construct();

        $params = $paramsObj->customParamList;

        if ( !OW::getUser()->isAuthenticated() )
        {
            $this->setVisible(false);
            return;
        }

        $language = OW::getLanguage();
        $eventService = EVENT_BOL_EventService::getInstance();

        $userEvents = $eventService->findUserEvents(OW::getUser()->getId(), null, $params['events_count'], true);
        $partEvents = $eventService->findUserParticipatedEvents(OW::getUser()->getId(), null, $params['events_count'], true);
        
        if ( empty($userEvents) && empty($partEvents) || !OW::getUser()->isAuthorized('event', 'view_event') )
        {
            $this->setVisible(false);
            return;
        }

        if ( !OW::getUser()->isAuthorized('event', 'add_event') && !OW::getUser()->isAuthorized('event') && !OW::getUser()->isAdmin())
        {
            $this->assign('noMenu', true);
        }

        if ( !$paramsObj->customizeMode )
        {
            $menuArray = array(
                array(
                    'label' => $language->text('event', 'dashboard_widget_menu_part_events_label'),
                    'id' => 'event_menu_part_id',
                    'contId' => 'event_menu_part_cont',
                    'active' => true
                ),
                array(
                    'label' => $language->text('event', 'dashboard_widget_menu_my_events_label'),
                    'id' => 'event_menu_my_id',
                    'contId' => 'event_menu_my_cont',
                    'active' => false
                )
            );

            $this->addComponent('listMenu', new BASE_CMP_WidgetMenu($menuArray));
        }
        else
        {
            $this->assign('noMenu', true);
        }

        $this->assign('my_events', $eventService->getListingDataWithToolbar($userEvents));

        $toolbarArray = array();

        if ( $eventService->findUsersEventsCount(OW::getUser()->getId()) > $params['events_count'] )
        {
            $toolbarArray['my'] = array(array('href' => OW::getRouter()->urlForRoute('event.view_event_list', array('list' => 'created')), 'label' => $language->text('event', 'view_all_label')));
        }

        $this->assign('part_events', $eventService->getListingDataWithToolbar($partEvents));

        if ( $eventService->findUserParticipatedEventsCount(OW::getUser()->getId()) > $params['events_count'] )
        {
            $toolbarArray['part'] = array(array('href' => OW::getRouter()->urlForRoute('event.view_event_list', array('list' => 'joined')), 'label' => $language->text('event', 'view_all_label')));
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
            self::SETTING_TITLE => OW::getLanguage()->text('event', 'my_events_widget_block_cap_label'),
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => self::ICON_CALENDAR
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}