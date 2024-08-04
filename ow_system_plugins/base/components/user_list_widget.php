<?php
/**
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_UserListWidget extends BASE_CMP_UsersWidget
{
    public function getData( BASE_CLASS_WidgetParameter $params )
    {
        $count = (int) $params->customParamList['count'];
        $language = OW::getLanguage();
        $userService = BOL_UserService::getInstance();

        $toolbar = array(
            'latest' => array(
                'label' => OW::getLanguage()->text('base', 'view_all'),
                'href' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'latest'))
            ),
            'online' => array(
                'label' => OW::getLanguage()->text('base', 'view_all'),
                'href' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'online'))
            ),
            'featured' => array(
                'label' => OW::getLanguage()->text('base', 'view_all'),
                'href' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'featured'))
            )
        );

        $latestUsersCount = $userService->count();

        if ( $latestUsersCount > $count )
        {
            $this->setSettingValue(self::SETTING_TOOLBAR, array($toolbar['latest']));
        }

        $resultList = array(
            'latest' => array(
                'menu-label' => $language->text('base', 'user_list_menu_item_latest'),
                'menu_active' => true,
                'userIds' => $this->getIdList($userService->findList(0, $count)),
                'toolbar' => ( $latestUsersCount > $count ? array($toolbar['latest']) : false ),
            ),
            'online' => array(
                'menu-label' => $language->text('base', 'user_list_menu_item_online'),
                'userIds' => $this->getIdList($userService->findOnlineList(0, $count)),
                'toolbar' => ( $userService->countOnline() > $count ? array($toolbar['online']) : false ),
            ));

        $featuredIdLIst = $this->getIdList($userService->findFeaturedList(0, $count));

        if ( !empty($featuredIdLIst) )
        {
            $resultList['featured'] = array(
                    'menu-label' => $language->text('base', 'user_list_menu_item_featured'),
                    'userIds' => $featuredIdLIst,
                    'toolbar' => ( $userService->countFeatured() > $count ? array($toolbar['featured']) : false ),
                );
        }

        $event = new OW_Event('base.userList.onToolbarReady', array(), $resultList);
        OW::getEventManager()->trigger($event);

        return $event->getData();
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => 'number',
            'label' => OW::getLanguage()->text('base', 'user_list_widget_settings_count'),
            'value' => '9'
        );

        return $settingList;
    }
    public static function validateSettingList( $settingList )
    {
        parent::validateSettingList($settingList);

        $validationMessage = OW::getLanguage()->text('base', 'user_list_widget_settings_count_msg');

        if ( !preg_match('/^\d+$/', $settingList['count']) )
        {
            throw new WidgetSettingValidateException($validationMessage, 'count');
        }
        if ( $settingList['count'] > 30 )
        {
            throw new WidgetSettingValidateException($validationMessage, 'count');
        }
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'user_list_widget_settings_title'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}