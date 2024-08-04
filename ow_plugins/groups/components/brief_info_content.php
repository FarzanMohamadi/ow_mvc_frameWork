<?php
/**
 * Group Brief Info Content
 *
 * @package ow_plugins.groups.components
 * @since 1.0
 */
class GROUPS_CMP_BriefInfoContent extends OW_Component
{

    /**
     * GROUPS_CMP_BriefInfoContent constructor.
     * @param $groupId
     * @param array $additionalInfo
     */
    public function __construct( $groupId, $additionalInfo = array())
    {
        parent::__construct();

        $plugin = OW::getPluginManager()->getPlugin('groups');
        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'groups.js');
        $service = GROUPS_BOL_Service::getInstance();
        $groupDto = null;
        if (isset($additionalInfo['group'])) {
            $groupDto = $additionalInfo['group'];
        }
        if ($groupDto == null ) {
            $groupDto = $service->findGroupById($groupId);
        }

        $desc = $groupDto->description;
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $desc)));
        if(isset($stringRenderer->getData()['string'])){
            $desc = ($stringRenderer->getData()['string']);
        }

        $parentTitle=null;
        $eventPrepareGroup = OW::getEventManager()->trigger(new OW_Event('on.prepare.group.data',['subGroupId'=>$groupId]));
        if(isset($eventPrepareGroup->getData()['parentData'])){
            $parentTitle = $eventPrepareGroup->getData()['parentData'];
        }


        $group = array(
            'title' => OW::getLanguage()->text('groups', 'group_title', array(
                'title' => htmlspecialchars($groupDto->title)
            )),
            'description' => $desc,
            'time' => $groupDto->timeStamp,
            'imgUrl' => empty($groupDto->imageHash) ? false : $service->getGroupImageUrl($groupDto, GROUPS_BOL_Service::IMAGE_SIZE_BIG),
            'url' => OW::getRouter()->urlForRoute('groups-view', array('groupId' => $groupDto->id)),
            "id" => $groupDto->id,
            "status" => $groupDto->status,
            "parentTitle" => $parentTitle
        );

        $imageUrl = empty($groupDto->imageHash) ? '' : $service->getGroupImageUrl($groupDto);
        OW::getDocument()->addMetaInfo('image', $imageUrl, 'itemprop');
        OW::getDocument()->addMetaInfo('og:image', $imageUrl, 'property');

        $createDate = UTIL_DateTime::formatDate($groupDto->timeStamp);
        $adminName = BOL_UserService::getInstance()->getDisplayName($groupDto->userId);
        $adminUrl = BOL_UserService::getInstance()->getUserUrl($groupDto->userId);

        $js = UTIL_JsGenerator::newInstance()
                ->jQueryEvent('#groups_toolbar_flag', 'click', UTIL_JsGenerator::composeJsString('OW.flagContent({$entityType}, {$entityId});',
                        array(
                            'entityType' => GROUPS_BOL_Service::FEED_ENTITY_TYPE,
                            'entityId' => $groupDto->id
                        )));

        OW::getDocument()->addOnloadScript($js, 1001);

        $toolbar = array();

        $groupInfo = array(
            'date' => array(
                'label' => OW::getLanguage()->text('groups', 'widget_brief_info_create_date', array('date' => $createDate)),
                'class' => 'group_details_create_date',
            ),
            'admin' => array(
                'label' => OW::getLanguage()->text('groups', 'widget_brief_info_admin', array('name' => $adminName, 'url' => $adminUrl)),
                'class' => 'group_details_admin_url',
            ),
            'members' => array(
                'label' => OW::getLanguage()->text('groups', 'widget_brief_info_member_count', array('numberOfMembers' => $service->findUserListCount($groupDto->getId()))),
                'class' => 'group_details_admin_url',
            ));
        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::GET_GROUP_SELECTED_CATEGORY_LABEL, array('groupId' => $groupId)));
        if(isset($resultsEvent->getData()['categoryLabel'])) {
            $groupCatUrl = OW::getRouter()->urlForRoute('groups-index').'?categoryStatus='.$resultsEvent->getData()['categoryStatus'];
            $toolbar [] =
                array(
                    'label' => OW::getLanguage()->text('frmgroupsplus', 'view_category_label', array('categoryLabel' => $resultsEvent->getData()['categoryLabel'], 'categoryUrl' => $groupCatUrl)),
                    'class' => 'group_details_view_category_label',
                );
        }
        $canEdit = false;
        $isCurrentUserManager = false;
        if (isset($additionalInfo['currentUserIsManager'])) {
            if (isset($additionalInfo['entityId']) && $additionalInfo['entityId'] == $groupDto->id) {
                $isCurrentUserManager = $additionalInfo['currentUserIsManager'];
            }
            if (isset($additionalInfo['group']) && $additionalInfo['group']->id == $groupId) {
                $isCurrentUserManager = $additionalInfo['currentUserIsManager'];
            }
        }

        $approveEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.add.approve.feature', array('groupStatus' => $groupDto->status,'groupId' => $groupDto->getId(), 'groupCreatorId' => $groupDto->userId)));
        if (isset($approveEvent->getData()['approveFeature']))
        {
            $toolbar[] = $approveEvent->getData()['approveFeature']['toolbarArray'];
        }


        $canEdit = $isCurrentUserManager || $service->isCurrentUserCanEdit($groupDto, false);
        if ($canEdit)
        {
            $toolbar[] = array(
                'label' => OW::getLanguage()->text('groups', 'edit_btn_label'),
                'href' => OW::getRouter()->urlForRoute('groups-edit', array('groupId' => $groupId)),
                'class' => 'group_details_edit_btn_label',
            );
        }

        if ( $groupDto->status == GROUPS_BOL_Group::STATUS_ACTIVE
                && OW::getUser()->isAuthenticated() 
                && OW::getUser()->getId() != $groupDto->userId )
        {
            $toolbar[] = array(
                'label' => OW::getLanguage()->text('base', 'flag'),
                'href' => 'javascript://',
                'id' => 'groups_toolbar_flag',
                'class' => 'group_details_groups_toolbar_flag',
            );
        }

        $userId = null;
        if (OW::getUser()->isAuthenticated()) {
            $userId = OW::getUser()->getId();
        }
        $checkManager = true;
        $checkUserExistInGroup = true;
        if (isset($additionalInfo['currentUserIsManager'])) {
            if (isset($additionalInfo['entityId']) && $additionalInfo['entityId'] == $groupId) {
                $checkManager = false;
            }
            if (isset($additionalInfo['group']) && $additionalInfo['group']->id == $groupId) {
                $checkManager = false;
            }
        }

        $isMemberOfGroup = false;
        if (isset($additionalInfo['currentUserIsMemberOfGroup'])) {
            if (isset($additionalInfo['entityId']) && $additionalInfo['entityId'] == $groupId) {
                $checkUserExistInGroup = false;
                $isMemberOfGroup = $additionalInfo['currentUserIsMemberOfGroup'];
            }
            if (isset($additionalInfo['group']) && $additionalInfo['group']->id == $groupId) {
                $checkUserExistInGroup = false;
                $isMemberOfGroup = $additionalInfo['currentUserIsMemberOfGroup'];
            }
        }

        if ($checkUserExistInGroup) {
            $isMemberOfGroup = GROUPS_BOL_Service::getInstance()->findUser($groupId, $userId) !== null;
        }

        if ($isCurrentUserManager || $service->isCurrentUserInvite($groupId, $checkManager, $checkUserExistInGroup, $groupDto)) {
            $idList = $service->getInvitableUserIds($groupId, $userId);

            $eventIisGroupsPlusCheckCanSearchAll = new OW_Event('frmgroupsplus.check.can.invite.all',array('checkAccess'=>true));
            OW::getEventManager()->trigger($eventIisGroupsPlusCheckCanSearchAll);
            if(isset($eventIisGroupsPlusCheckCanSearchAll->getData()['directInvite']) && $eventIisGroupsPlusCheckCanSearchAll->getData()['directInvite']==true){
                $title = OW::getLanguage()->text('frmgroupsplus', 'add_to_group_title');
            }
            else if(isset($eventIisGroupsPlusCheckCanSearchAll->getData()['hasAccess']) && $eventIisGroupsPlusCheckCanSearchAll->getData()['hasAccess']==true){
                $title = OW::getLanguage()->text('groups', 'invite_fb_title_all_users');
            }
            else{
                $title = OW::getLanguage()->text('groups', 'invite_fb_title');
            }

            $enableQRSearch = !(boolean)OW::getConfig()->getValue('groups','enable_QRSearch');
            $options = array(
                'groupId' => $groupId,
                'userList' => $idList,
                'floatBoxTitle' => $title,
                'inviteResponder' => OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'invite'),
                'defaultSearch' => $enableQRSearch
            );
            $js = UTIL_JsGenerator::newInstance()->callFunction('GROUPS_InitInviteButton', array($options));
            OW::getDocument()->addOnloadScript($js);

            $toolbar[] = array(
                'label' => OW::getLanguage()->text('groups', 'widget_invite_button_title'),
                'href' => 'javascript://',
                'id' => 'GROUPS_InviteLink',
                'class' => 'group_details_invite_btn_label'
            );
        }


        if ( $userId != null && $groupDto->userId != $userId &&  $isMemberOfGroup )
        {
            $actionUrl = OW::getRouter()->urlForRoute('groups-leave', array('groupId' => $groupId));
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$groupId,'isPermanent'=>true,'activityType'=>'leave_group')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $code = $frmSecuritymanagerEvent->getData()['code'];
                $actionUrl = OW::getRequest()->buildUrlQueryString($actionUrl, array('code' => $code));
            }
            $this->assign('actionUrl', $actionUrl);

            $js = UTIL_JsGenerator::newInstance();
            $js->newFunction('window.location.href=url', array('url'), 'redirect');

            $lang = OW::getLanguage()->text('groups', 'leave_group_confirm_msg');
            $js->jQueryEvent('#leave-group_btn', 'click', UTIL_JsGenerator::composeJsString(
                'var jc = $.confirm({$lang}); jc.buttons.ok.action = function () {redirect({$url});}', array('url' => $actionUrl, 'lang' => $lang)));
            
            OW::getDocument()->addOnloadScript($js);

            $toolbar[] = array(
                'label' => OW::getLanguage()->text('groups', 'widget_leave_button'),
                'href' => 'javascript://',
                'id' => 'leave-group_btn',
                'class' => 'group_details_leave_btn_label'
            );
        }

        $event = new BASE_CLASS_EventCollector('groups.on_toolbar_collect', array('groupId' => $groupId));
        OW::getEventManager()->trigger($event);

        foreach ( $event->getData() as $item )
        {
            $toolbar[] = $item;
        }

        // add join button
        if(!$isMemberOfGroup){
            $joinUrl = OW::getRouter()->urlForRoute('groups-join', array('groupId' => $groupId));
            $frmSecurityManagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$groupId,'isPermanent'=>true,'activityType'=>'join_group')));
            if(isset($frmSecurityManagerEvent->getData()['code'])){
                $code = $frmSecurityManagerEvent->getData()['code'];
                $joinUrl = OW::getRequest()->buildUrlQueryString($joinUrl, array('code' => $code));
            }
            $toolbar[] = array(
                'label' => OW::getLanguage()->text('groups', 'widget_join_button'),
                'href' => $joinUrl,
                'id' => 'leave-group_btn',
                'class' => 'group_details_join_btn_label'
            );
        }

        $this->assign('toolbar', $toolbar);

        $this->assign('groupInfo', $groupInfo );

        $this->assign('group', $group);
    }
}