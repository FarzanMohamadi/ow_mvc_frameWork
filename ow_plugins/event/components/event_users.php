<?php
/**
 * User console component class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.components
 * @since 1.0
 */
class EVENT_CMP_EventUsers extends BASE_CLASS_Widget
{
    private $eventService;

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $params  )
    {
        parent::__construct();

        $this->eventService = EVENT_BOL_EventService::getInstance();
        $eventId = $params->additionalParamList['entityId'];

        $event = $this->eventService->findEvent($eventId);

        if ( $event === null )
        {
            $this->setVisible(false);
        }

        // event users info
        if($event != null) {
            $this->addUserList($event, EVENT_BOL_EventService::USER_STATUS_YES);
            $this->addUserList($event, EVENT_BOL_EventService::USER_STATUS_MAYBE);
            $this->addUserList($event, EVENT_BOL_EventService::USER_STATUS_NO);
        }
        $this->assign('userLists', $this->userLists);
        $this->addComponent('userListMenu', new BASE_CMP_WidgetMenu($this->userListMenu));
    }
    private $userLists;
    private $userListMenu;

    private function addUserList( EVENT_BOL_Event $event, $status )
    {
        $configs = $this->eventService->getConfigs();

        $language = OW::getLanguage();
        $listTypes = $this->eventService->getUserListsArray();
        $serviceConfigs = $this->eventService->getConfigs();
        $userList = $this->eventService->findEventUsers($event->getId(), $status, null, $configs[EVENT_BOL_EventService::CONF_EVENT_USERS_COUNT]);
        $usersCount = $this->eventService->findEventUsersCount($event->getId(), $status);

        $idList = array();

        /* @var $eventUser EVENT_BOL_EventUser */
        foreach ( $userList as $eventUser )
        {
            $idList[] = $eventUser->getUserId();
        }

        $usersCmp = new BASE_CMP_AvatarUserList($idList);

        $linkId = UTIL_HtmlTag::generateAutoId('link');
        $contId = UTIL_HtmlTag::generateAutoId('cont');

        $this->userLists[] = array(
            'contId' => $contId,
            'cmp' => $usersCmp->render(),
            'bottomLinkEnable' => ($usersCount > $serviceConfigs[EVENT_BOL_EventService::CONF_EVENT_USERS_COUNT]),
            'toolbarArray' => array(
                array(
                    'label' => $language->text('event', 'avatar_user_list_bottom_link_label', array('count' => $usersCount)),
                    'href' => OW::getRouter()->urlForRoute('event.user_list', array('eventId' => $event->getId(), 'list' => $listTypes[(int) $status]))
                )
            )
        );

        $this->userListMenu[] = array(
            'label' => $language->text('event', 'avatar_user_list_link_label_' . $status),
            'id' => $linkId,
            'contId' => $contId,
            'active' => ( is_array($this->userListMenu) && sizeof($this->userListMenu) < 1 ? true : false )
        );

        if(sizeof($this->userListMenu) == 1){
            OW::getDocument()->addOnloadScript('$("#'.$linkId.'").click();', 9999999);
        }
    }

    public static function getSettingList() {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW_Language::getInstance()->text('event', 'event_widget_settings_count'),
            'value' => 10
        );

        return $settingList;
    }

    public static function getStandardSettingValueList() {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('event', 'view_page_users_block_cap_label'),
            self::SETTING_ICON => self::ICON_FILE
        );
    }

    public static function getAccess() {
        return self::ACCESS_ALL;
    }

}