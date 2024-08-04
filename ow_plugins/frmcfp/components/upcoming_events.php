<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcfp.components
 * @since 1.0
 */
class FRMCFP_CMP_UpcomingEvents extends BASE_CLASS_Widget
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
        $eventService = FRMCFP_BOL_Service::getInstance();
        //$events = $eventService->findPublicEvents(null, $params['events_count']);
        $events = $eventService->findUpComingEventsForUser($params['events_count'],$userId);
       // $count = $eventService->findPublicEventsCount();
        $count = $eventService->findEventsForUserCount($userId, null, false, null, true, $isPublic, null);

        if ( ( !OW::getUser()->isAuthenticated() || (!OW::getUser()->isAuthorized('frmcfp', 'add_event') && !OW::getUser()->isAuthorized('frmcfp') && !OW::getUser()->isAdmin() )) && $count == 0 )
        {
            $this->setVisible(false);
            return;
        }

        $this->assign('events', $eventService->getListingDataWithToolbar($events));
        $this->assign('no_content_message', OW::getLanguage()->text('frmcfp', 'no_index_events_label', array('url' => OW::getRouter()->urlForRoute('frmcfp.add'))));

        if ( $eventService->findPublicEventsCount() > $params['events_count'] )
        {
            $toolbarArray = array(array('href' => OW::getRouter()->urlForRoute('frmcfp.view_event_list', array('list' => 'latest')), 'label' => OW::getLanguage()->text('frmcfp', 'view_all_label')));
            $this->assign('toolbar', $toolbarArray);
        }
    }

    public static function getSettingList()
    {
        $eventConfigs = FRMCFP_BOL_Service::getInstance()->getConfigs();
        $settingList = array();
        $settingList['events_count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('frmcfp', 'cmp_widget_events_count'),
            'optionList' => $eventConfigs[FRMCFP_BOL_Service::CONF_WIDGET_EVENTS_COUNT_OPTION_LIST],
            'value' => $eventConfigs[FRMCFP_BOL_Service::CONF_WIDGET_EVENTS_COUNT]
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('frmcfp', 'up_events_widget_block_cap_label'),
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_ICON => self::ICON_CALENDAR
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}