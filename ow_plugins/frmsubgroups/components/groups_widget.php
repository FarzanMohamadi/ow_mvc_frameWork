<?php
/**
 * Class GROUPS_CMP_GroupsWidget
 */
class FRMSUBGROUPS_CMP_GroupsWidget extends BASE_CLASS_Widget
{
    /**
     *
     * @var GROUPS_BOL_Service
     */
    private $groupService;

    /**
     *
     * @var FRMSUBGROUPS_BOL_Service
     */
    private $subGroupService;

    private $showCreate = true;

    /**
     * FRMSUBGROUPS_CMP_GroupsWidget constructor.
     * @param BASE_CLASS_WidgetParameter $paramObj
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();
        $this->groupService = GROUPS_BOL_Service::getInstance();
        $this->subGroupService = FRMSUBGROUPS_BOL_Service::getInstance();

        if ( !$this->groupService->isCurrentUserCanCreate() )
        {
            $authStatus = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'create');
            $this->showCreate = $authStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED;

            if ( $this->showCreate )
            {
                $script = UTIL_JsGenerator::composeJsString('$("#groups-create-btn-c a").click(function(){
                    OW.authorizationLimitedFloatbox({$msg});
                    return false;
                });', array(
                    "msg" => $authStatus["msg"]
                ));
                OW::getDocument()->addOnloadScript($script);
            }
        }

        $count = isset($paramObj->customParamList['count']) ? (int) $paramObj->customParamList['count'] : 8;

        $this->assign('showTitles', !empty($paramObj->customParamList['showTitles']));

        $popular= $this->subGroupService->findPopularGroupList(0, $count);
        $latest = $this->subGroupService->findLatestGroupList(0, $count);

        $toolbars = self::getToolbar();

        $lang = OW::getLanguage();
        $menuItems = array();

        if ( $this->assignList('latest', $latest) )
        {
            $this->setSettingValue(self::SETTING_TOOLBAR, $toolbars['latest']);
            $menuItems[] = array(
                'label' => $lang->text('groups', 'group_list_menu_item_latest'),
                'id' => 'frmsubgroups-widget-menu-latest',
                'contId' => 'frmsubgroups-widget-latest',
                'active' => true
            );
        }

        if ( $this->assignList('popular', $popular) )
        {
            $menuItems[] = array(
                'label' => $lang->text('groups', 'group_list_menu_item_popular'),
                'id' => 'frmsubgroups-widget-menu-popular',
                'contId' => 'frmsubgroups-widget-popular',
                'active' => empty($menuItems)
            );
        }

        if ( empty($menuItems) && !$this->showCreate )
        {
            $this->setVisible(false);

            return;
        }

        $this->assign('menuItems', $menuItems);

        if ( $paramObj->customizeMode )
        {
            $this->assign('menu', '');
        }
        else
        {
            $this->addComponent('menu', new BASE_CMP_WidgetMenu($menuItems));
        }

        $this->assign('toolbars', $toolbars);
        $this->assign('createUrl', OW::getRouter()->urlForRoute('groups-create'));
    }

    private function assignList( $listName, $list )
    {
        $groupIdList = array();

        foreach ( $list as $item )
        {
            $groupIdList[] = $item->id;
        }

        $userCountList = $this->groupService->findUserCountForList($groupIdList);

        $tplList = array();
        foreach ( $list as $item )
        {
            $eventPrepareGroup = OW::getEventManager()->trigger(new OW_Event('on.prepare.group.data',['parentGroupId'=>isset($item->parentGroupId)? $item->parentGroupId : null]));
            $parentTitle=null;
            if(isset($eventPrepareGroup->getData()['parentData'])){
                $parentTitle = $eventPrepareGroup->getData()['parentData'];
            }
            /* @var $item GROUPS_BOL_Group */
            $imageUrl = $this->groupService->getGroupImageUrl($item);
            $tplList[] = array(
                'image' => $imageUrl,
                'imageInfo' => BOL_AvatarService::getInstance()->getAvatarInfo($item->id, $imageUrl, 'group'),
                'title' => htmlspecialchars($item->title),
                'url' => OW::getRouter()->urlForRoute('groups-view', array('groupId' => $item->id)),
                'users' => $userCountList[$item->id],
                'unreadCount' => GROUPS_BOL_Service::getInstance()->getUnreadCountForGroupUser($item->id),
                'parentTitle' => $parentTitle
            );
        }

        $this->assign($listName, $tplList);

        return!empty($tplList);
    }

    private static function getToolbar()
    {
        $lang = OW::getLanguage();

        $toolbars['latest'] = array();
        $showCreate = true;

        if ( !GROUPS_BOL_Service::getInstance()->isCurrentUserCanCreate() )
        {
            $authStatus = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'create');
            $showCreate = $authStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED;
        }

        if ( $showCreate && OW::getUser()->isAuthenticated())
        {
            $toolbars['latest'][] = array(
                'href' => OW::getRouter()->urlForRoute('groups-create'),
                'label' => $lang->text('groups', 'add_new'),
                "id" => "groups-create-btn-c"
            );
        }

        $toolbars['latest'][] = array(
            'href' => OW::getRouter()->urlForRoute('groups-latest'),
            'label' => $lang->text('base', 'view_all')
        );

        $toolbars['popular'] = array();

        if ( $showCreate && OW::getUser()->isAuthenticated() )
        {
            $toolbars['popular'][] = array(
                'href' => OW::getRouter()->urlForRoute('groups-create'),
                'label' => $lang->text('groups', 'add_new'),
                "id" => "groups-create-btn-c"
            );
        }

        $toolbars['popular'][] = array(
            'href' => OW::getRouter()->urlForRoute('groups-most-popular'),
            'label' => $lang->text('base', 'view_all')
        );

        return $toolbars;
    }

    public static function getSettingList()
    {
        $settingList = array();

        $settingList['count'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW::getLanguage()->text('groups', 'widget_groups_count_setting'),
            'value' => 3
        );

        $settingList['showTitles'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => OW::getLanguage()->text('groups', 'widget_groups_show_titles_setting'),
            'value' => true
        );

        return $settingList;
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('frmsubgroups', 'widget_groups_title'),
            self::SETTING_ICON => self::ICON_COMMENT,
            self::SETTING_SHOW_TITLE => true
        );
    }
}