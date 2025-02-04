<?php
/**
 * Groups
 *
 * @package ow_plugins.groups.controllers
 * @since 1.0
 */
class GROUPS_CTRL_Groups extends OW_ActionController
{
    /**
     *
     * @var GROUPS_BOL_Service
     */
    private $service;

    public function __construct()
    {
        $this->service = GROUPS_BOL_Service::getInstance();
        $this->setDocumentKey('group_users_list_page');

        if ( !OW::getRequest()->isAjax() )
        {
            $plugin = OW::getPluginManager()->getPlugin('groups');
            OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'groups.js');
            $mainMenuItem = OW::getDocument()->getMasterPage()->getMenu(OW_Navigation::MAIN)->getElement('main_menu_list', 'groups');
            if ( $mainMenuItem !== null )
            {
                $mainMenuItem->setActive(true);
            }
        }
    }

    public function index()
    {
        if(OW::getUser()->isAuthenticated()) {
            $this->myGroupList();
        }else{
            $this->latestList();
        }
    }

    public function customize( $params )
    {
        $params['mode'] = 'customize';

        $this->view($params);
    }

    public function view( $params )
    {
        $groupId = (int) $params['groupId'];

        $this->setDocumentKey('ow_group_page');

        if ( empty($groupId) )
        {
            throw new Redirect404Exception();
        }

        $groupDto = $this->service->findGroupById($groupId);

        if ( $groupDto === null )
        {
            throw new Redirect404Exception();
        }

        $language = OW::getLanguage();

        if ( !$this->service->isCurrentUserCanView($groupDto, true) )
        {
            throw new Redirect404Exception();
        }

        $invite = $this->service->findInvite($groupDto->id, OW::getUser()->getId());

        if ( $invite !== null )
        {
            OW::getRegistry()->set('groups.hide_console_invite_item', true);

            $this->service->markInviteAsViewed($groupDto->id, OW::getUser()->getId());
        }

        if ( $groupDto->whoCanView == GROUPS_BOL_Service::WCV_INVITE && !OW::getUser()->isAuthorized('groups') )
        {
            if ( !OW::getUser()->isAuthenticated() )
            {
                throw new Redirect404Exception();
            }

            $user = $this->service->findUser($groupDto->id, OW::getUser()->getId());

            if ( $groupDto->whoCanView == GROUPS_BOL_Service::WCV_INVITE && $invite === null && $user === null && !OW::getUser()->isAdmin())
            {
                $isFromInvitationLink = false;
                if (FRMSecurityProvider::checkPluginActive('frmgroupsinvitationlink', true)){
                    $isFromInvitationLink = FRMGROUPSINVITATIONLINK_BOL_Service::getInstance()
                        ->isUserVisitedGroupLink($groupDto->id, OW::getUser()->getId());
                }
                if(!$isFromInvitationLink){
                    throw new Redirect404Exception();
                }
            }
        }

        OW::getDocument()->setTitle($language->text('groups', 'view_page_title', array(
            'group_name' => strip_tags($groupDto->title)
        )));

        OW::getDocument()->setDescription($language->text('groups', 'view_page_description', array(
            'description' => UTIL_String::truncate(strip_tags($groupDto->description), 200)
        )));

        $place = 'group';

        $customizeUrls = array(
            'customize' => OW::getRouter()->urlForRoute('groups-customize', array('mode' => 'customize', 'groupId' => $groupId)),
            'normal' => OW::getRouter()->urlForRoute('groups-view', array('groupId' => $groupId))
        );

        $componentAdminService = BOL_ComponentAdminService::getInstance();
        $componentEntityService = BOL_ComponentEntityService::getInstance();

        $userCustomizeAllowed = $componentAdminService->findPlace($place)->editableByUser;
        $ownerMode = $groupDto->userId == OW::getUser()->getId();
        $allowCustomize = $ownerMode || OW::getUser()->isAuthorized("groups");

        $customize = !empty($params['mode']) && $params['mode'] == 'customize';

        if ( !( $userCustomizeAllowed && $allowCustomize ) && $customize )
        {
            $this->redirect($customizeUrls['normal']);
        }

        $template = $customize ? 'drag_and_drop_entity_panel_customize' : 'drag_and_drop_entity_panel';

        $schemeList = $componentAdminService->findSchemeList();
        $defaultScheme = $componentAdminService->findSchemeByPlace($place);
        if ( empty($defaultScheme) && !empty($schemeList) )
        {
            $defaultScheme = reset($schemeList);
        }

        if ( !$componentAdminService->isCacheExists($place) )
        {
            $state = array();
            $state['defaultComponents'] = $componentAdminService->findPlaceComponentList($place);
            $state['defaultPositions'] = $componentAdminService->findAllPositionList($place);
            $state['defaultSettings'] = $componentAdminService->findAllSettingList();
            $state['defaultScheme'] = $defaultScheme;

            $componentAdminService->saveCache($place, $state);
        }

        $state = $componentAdminService->findCache($place);

        $defaultComponents = $state['defaultComponents'];
        $defaultPositions = $state['defaultPositions'];
        $defaultSettings = $state['defaultSettings'];
        $defaultScheme = $state['defaultScheme'];

        if ( $userCustomizeAllowed )
        {
            if ( !$componentEntityService->isEntityCacheExists($place, $groupId) )
            {
                $entityCache = array();
                $entityCache['entityComponents'] = $componentEntityService->findPlaceComponentList($place, $groupId);
                $entityCache['entitySettings'] = $componentEntityService->findAllSettingList($groupId);
                $entityCache['entityPositions'] = $componentEntityService->findAllPositionList($place, $groupId);

                $componentEntityService->saveEntityCache($place, $groupId, $entityCache);
            }

            $entityCache = $componentEntityService->findEntityCache($place, $groupId);
            $entityComponents = $entityCache['entityComponents'];
            $entitySettings = $entityCache['entitySettings'];
            $entityPositions = $entityCache['entityPositions'];
        }
        else
        {
            $entityComponents = array();
            $entitySettings = array();
            $entityPositions = array();
        }

        $isChannel = false;
        $hideCommentFeatures = false;
        $hideLikeFeatures = false;
        $showGroupChatForm = false;
        $canReply = false;

        $managerIds = array();
        if (FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)) {
            $groupManagerIds = FRMGROUPSPLUS_BOL_GroupManagersDao::getInstance()->getGroupManagersByGroupIds(array($groupId));
            $managerIds = array();
            if (isset($groupManagerIds[$groupId])) {
                $managerIds = $groupManagerIds[$groupId];
            }
        }
        $isCurrentUserManager = in_array(OW::getUser()->getId(), $managerIds);
        $additionalInfo = array(
            'isManager' => $isCurrentUserManager,
            'group_object' => $groupDto,
        );
        $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.load', array('groupId' => $groupId, 'group' => $groupDto, 'additionalInfo' => $additionalInfo)));
        if (isset($channelEvent->getData()['isChannel']) && $channelEvent->getData()['isChannel'] == true) {
            $isChannel = true;
        }
        if ((isset($channelEvent->getData()['hideCommentFeatures']) && $channelEvent->getData()['hideCommentFeatures'] == true)) {
            $hideCommentFeatures = true;
        }
        if ((isset($channelEvent->getData()['hideLikeFeatures']) && $channelEvent->getData()['hideLikeFeatures'] == true)) {
            $hideLikeFeatures = true;
        }
        if(isset($channelEvent->getData()['showGroupChatForm'])){
            $showGroupChatForm = $channelEvent->getData()['showGroupChatForm'];
        }
        $isMemberOfGroup = GROUPS_BOL_Service::getInstance()->findUser($groupId, OW::getUser()->getId()) !== null;
        if(isset($channelEvent->getData()['canReply']) && $channelEvent->getData()['canReply'] && $isMemberOfGroup){
            $canReply = true;
        }

        $componentPanel = new BASE_CMP_DragAndDropEntityPanel($place, $groupId, $defaultComponents, $customize, $template);
        $AdditionalSettingList = array(
            'entityId' => $groupId,
            'entity' => 'groups',
            'group' => $groupDto,
            'isChannel' => $isChannel,
            'hideCommentFeatures' => $hideCommentFeatures,
            'hideLikeFeatures' => $hideLikeFeatures,
            'showGroupChatForm' => $showGroupChatForm,
            'canReplyInGroup' => $canReply,
            'currentUserIsMemberOfGroup' => $isMemberOfGroup,
            'currentUserIsManager' => $isCurrentUserManager,
        );
        $componentPanel->setAdditionalSettingList($AdditionalSettingList);

        if ( $allowCustomize )
        {
            $componentPanel->allowCustomize($userCustomizeAllowed);
            $componentPanel->customizeControlCunfigure($customizeUrls['customize'], $customizeUrls['normal']);
        }

        $componentPanel->setSchemeList($schemeList);
        $componentPanel->setPositionList($defaultPositions);
        $componentPanel->setSettingList($defaultSettings);
        $componentPanel->setScheme($defaultScheme);

        /*
         * This feature was disabled for users
         * if ( !empty($userScheme) )
          {
          $componentPanel->setUserScheme($userScheme);
          } */

        if ( !empty($entityComponents) )
        {
            $componentPanel->setEntityComponentList($entityComponents);
        }

        if ( !empty($entityPositions) )
        {
            $componentPanel->setEntityPositionList($entityPositions);
        }

        if ( !empty($entitySettings) )
        {
            $componentPanel->setEntitySettingList($entitySettings);
        }

        $this->assign('componentPanel', $componentPanel->render());

        $decodedString=$groupDto->description;
        $stringDecode = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ,array('toDecode' => $decodedString)));
        if(isset($stringDecode->getData()['decodedString'])){
            $decodedString = $stringDecode->getData()['decodedString'];
        }
        $params = array(
            "sectionKey" => "groups",
            "entityKey" => "groupPage",
            "title" => "groups+meta_title_groups_page",
            "description" => "groups+meta_desc_groups_page",
            "keywords" => "groups+meta_keywords_groups_page",
            "vars" => array( "group_title" => $groupDto->title, "group_description" =>$decodedString )
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));

        if (isset($_GET['replyToUsername']) && isset($_GET['replyToId'])){
            $replyToText = OW::getLanguage()->text('groups', 'in_reply_to', ['author'=>$_GET['replyToUsername']]);
            $js = " 
                $(document).ready(function(){
                    addPostReplyTo(" . $_GET['replyToId'] . ", '" . $replyToText ."');
                });";
            OW::getDocument()->addOnloadScript($js);
        }
    }

    public function create()
    {
        if (!OW::getUser()->isAuthenticated()) {
            throw new AuthenticateException();
        }

        if (!$this->service->isCurrentUserCanCreate()) {
            $permissionStatus = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'create');

            throw new AuthorizationException($permissionStatus['msg']);
        }

        $language = OW::getLanguage();

        OW::getDocument()->setHeading($language->text('groups', 'create_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_new');
        OW::getDocument()->setTitle($language->text('groups', 'create_page_title'));
        OW::getDocument()->setDescription($language->text('groups', 'create_page_description'));

        $form = new GROUPS_CreateGroupForm();
        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ADD_GROUP_FILTER_ELEMENT,
            array('form' => $form)));
        if (isset($resultsEvent->getData()['hasCategoryFilter'])) {
            $this->assign('hasCategoryFilter', true);
        }
        if (isset($resultsEvent->getData()['hasReportElement'])) {
            $this->assign('hasReportElement', true);
        }
        $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.set.channel.group',
            array('form' =>$form,'groupId'=>null)));
        if(isset($channelEvent->getData()['isChannel'])) {
            $this->assign('hasChannelEnable', true);
            $form = $channelEvent->getData()['form'];
        }

        $groupSettingEvent = OW::getEventManager()->trigger(new OW_Event('add.group.setting.elements',
            array('form' =>$form,'groupId'=>null)));
        if(isset($groupSettingEvent->getData()['uploadFile'])) {
            $this->assign('hasUploadFile', true);
        }
        if(isset($groupSettingEvent->getData()['form']))
        {
            $form = $groupSettingEvent->getData()['form'];
        }
        if(isset($groupSettingEvent->getData()['createTopic'])) {
            $this->assign('hasCreateTopic', true);
        }
        if(isset($groupSettingEvent->getData()['rssLinks'])) {
            $this->assign('hasRSS', true);
        }
        if(isset($groupSettingEvent->getData()['parentGroupTitle'])) {
            $parentTitle= $groupSettingEvent->getData()['parentGroupTitle'];
            OW::getDocument()->setHeading($language->text('frmsubgroups', 'create_heading',['parentGroupTitle'=>$parentTitle]));
        }

        /**
         * if group must be approved by group moderators or admins, a proper message must show in group creation page
         */
        //Begin
        $feedback ='';
        $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.after.group.create.approve.feedback');
        OW::getEventManager()->trigger($eventIisGroupsPlusManager);
        if(isset($eventIisGroupsPlusManager->getData()['feedback'])){
            $feedback=$eventIisGroupsPlusManager->getData()['feedback'];
            $this->assign('UnapprovedGroupWarning',true);
            $this->assign('UnapprovedGroupWarningText',$eventIisGroupsPlusManager->getData()['feedbackText']);
        }
        //End
        if(isset($eventIisGroupsPlusManager->getData()['form'])){
            $form = $eventIisGroupsPlusManager->getData()['form'];
        }

        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $groupDto = GROUPS_BOL_Service::getInstance()->createGroup(OW::getUser()->getId(), $form->getValues());
            if (empty($groupDto)) {
                $this->redirect();
            }
            OW::getFeedback()->info($language->text('groups', 'create_success_msg').$feedback);
            /*
             * user can't see the created group until it activates
             */
            if(isset($eventIisGroupsPlusManager->getData()['needsApprove']) && $eventIisGroupsPlusManager->getData()['needsApprove'] ==true){
                $this->redirect(OW::getRouter()->urlForRoute('groups-latest'));
            }else{
                $this->redirect($this->service->getGroupUrl($groupDto));
            }
        }

        $this->addForm($form);

        $this->setDocumentKey("groups_create");
    }

    public function delete( $params )
    {
        if ( empty($params['groupId']) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code = $_GET['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'delete_group')));
        }

        $groupDto = $this->service->findGroupById($params['groupId']);

        if ( empty($groupDto) )
        {
            throw new Redirect404Exception();
        }

        $isOwner = OW::getUser()->getId() == $groupDto->userId;
        $isModerator = OW::getUser()->isAuthorized('groups');
        $isAdmin = OW::getUser()->isAdmin();

        $isManager=false;
        $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.check.user.manager.status', array('groupId'=>$params['groupId']));
        OW::getEventManager()->trigger($eventIisGroupsPlusManager);
        if(isset($eventIisGroupsPlusManager->getData()['isUserManager'])){
            $isManager=$eventIisGroupsPlusManager->getData()['isUserManager'];
        }
        if ( !$isOwner && !$isModerator  && !$isManager && !$isAdmin )
        {
            throw new Redirect404Exception();
        }

        $this->service->deleteGroup($groupDto->id);
        OW::getFeedback()->info(OW::getLanguage()->text('groups', 'delete_complete_msg'));

        $this->redirect(OW::getRouter()->urlForRoute('groups-index'));
    }

    public function edit( $params )
    {
        $groupId = (int) $params['groupId'];

        if ( empty($groupId) )
        {
            throw new Redirect404Exception();
        }

        $groupDto = $this->service->findGroupById($groupId);

        if ( !isset($groupDto) )
        {
            throw new Redirect404Exception();
        }

        if ( !$this->service->isCurrentUserCanEdit($groupDto) )
        {
            throw new Redirect404Exception();
        }

        $form = new GROUPS_EditGroupForm($groupDto);
        $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.check.user.manager.status', array('groupId'=>$params['groupId']));
        OW::getEventManager()->trigger($eventIisGroupsPlusManager);
        if(isset($eventIisGroupsPlusManager->getData()['isUserManager'])){
            $isUserManager = true;
            $this->assign('isUserManager',$isUserManager);
        }
        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ADD_GROUP_FILTER_ELEMENT,
            array('form' =>$form,'groupId'=>$groupId)));
        if(isset($resultsEvent->getData()['hasCategoryFilter'])) {
            $this->assign('hasCategoryFilter',true);
            $form = $resultsEvent->getData()['form'];
        }
        if (isset($resultsEvent->getData()['hasReportElement'])) {
            $this->assign('hasReportElement', true);
            $form = $resultsEvent->getData()['form'];
        }
        $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.set.channel.group',
            array('form' =>$form,'groupId'=>$groupId)));
        if(isset($channelEvent->getData()['isChannel'])) {
            $this->assign('hasChannelEnable', true);
            $form = $channelEvent->getData()['form'];
        }

        $groupSettingEvent = OW::getEventManager()->trigger(new OW_Event('add.group.setting.elements',
            array('form' =>$form,'groupId'=>$groupId)));
        if(isset($groupSettingEvent->getData()['uploadFile'])) {
            $this->assign('hasUploadFile', true);
            $form = $groupSettingEvent->getData()['form'];
        }
        if(isset($groupSettingEvent->getData()['createTopic'])) {
            $this->assign('hasCreateTopic', true);
        }
        if(isset($groupSettingEvent->getData()['rssLinks'])) {
            $this->assign('hasRSS', true);
        }

        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::GET_GROUP_SELECTED_CATEGORY_ID, array('groupId' => $params['groupId'])));
        if(isset($resultsEvent->getData()['selectedCategoryId'])) {
            $form->getElement('categoryStatus')->setValue($resultsEvent->getData()['selectedCategoryId']);
        }
        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            if ( $form->process() )
            {
                OW::getFeedback()->info(OW::getLanguage()->text('groups', 'edit_success_msg'));
            }
            $this->redirect($this->service->getGroupUrl($groupDto));
        }

        $this->addForm($form);

        $this->assign('imageUrl', empty($groupDto->imageHash) ? false : $this->service->getGroupImageUrl($groupDto));

        $viewUrl = $this->service->getGroupUrl($groupDto);

        $js = UTIL_JsGenerator::newInstance();
        $js->newFunction('window.location.href=url', array('url'), 'redirect');
        $deleteUrl = OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'delete', array('groupId' => $groupDto->id));
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$groupDto->id,'isPermanent'=>true,'activityType'=>'delete_group')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $code = $frmSecuritymanagerEvent->getData()['code'];
            $deleteUrl = OW::getRequest()->buildUrlQueryString($deleteUrl, array(
                'code' =>$code
            ));
        }

        $lang = OW::getLanguage()->text('groups', 'delete_confirm_msg');
        $jDeleteCodes = 'var jc = $.confirm({$lang});jc.buttons.ok.action = function () {  redirect({$url});  $(\'#groups-delete_btn\').addClass(\'ow_inprogress\'); };';
        $jDeleteParams = array('url' => $deleteUrl, 'lang' => $lang);
        $eventGroupButtons= OW::getEventManager()->trigger(new OW_Event('on.render.group.edit.buttons',
            array('groupId'=>$groupDto->id)));
        if(isset($eventGroupButtons->getData()['subGroupsTitles'])){
            $jDeleteCodes = 'var jc = $.confirm({$lang});jc.buttons.ok.action = function () {var jcc = $.confirm({$subGroupsTitles});jcc.buttons.ok.action = function () {  redirect({$url});  $(\'#groups-delete_btn\').addClass(\'ow_inprogress\'); };};';
            $jDeleteParams['subGroupsTitles']=$eventGroupButtons->getData()['subGroupsTitles'];
        }

        $js->jQueryEvent('#groups-delete_btn', 'click', UTIL_JsGenerator::composeJsString(
            $jDeleteCodes, $jDeleteParams));
        $js->jQueryEvent('#groups-back_btn', 'click', UTIL_JsGenerator::composeJsString(
                'redirect({$url});', array('url' => $viewUrl)));
        $js->jQueryEvent('#groups-cancel_edit', 'click', UTIL_JsGenerator::composeJsString(
                'redirect({$url});', array('url' => $viewUrl)));

        OW::getDocument()->addOnloadScript($js);
    }

    public function join( $params )
    {
        if ( empty($params['groupId']) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $groupId = (int) $params['groupId'];
        $userId = OW::getUser()->getId();
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            if(!isset($params['code']) && !isset($_GET['code'])){
                throw new Redirect404Exception();
            }
            $code = isset($params['code'])?$params['code']:$_GET['code'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => $userId, 'code'=>$code,'activityType'=>'join_group')));
        }
        $groupDto = $this->service->findGroupById($groupId);

        if ( $groupDto === null )
        {
            throw new Redirect404Exception();
        }
        
        if ( $groupDto->userId == OW::getUser()->getId() || !OW::getUser()->isAuthorized('groups', 'view') )
        {
            throw new Redirect404Exception();
        }

        $invite = $this->service->findInvite($groupDto->id, $userId);

        if ( $invite !== null )
        {
            $this->service->markInviteAsViewed($groupDto->id, $userId);
        }
        else if ( $groupDto->whoCanView == GROUPS_BOL_Service::WCV_INVITE  && !OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('groups'))
        {
            throw new Redirect404Exception();
        }

        GROUPS_BOL_Service::getInstance()->addUser($groupId, $userId);

        $redirectUrl = OW::getRouter()->urlForRoute('groups-view', array('groupId' => $groupId));
        OW::getFeedback()->info(OW::getLanguage()->text('groups', 'join_complete_message'));
        $this->redirect($redirectUrl);
    }

    public function declineInvite( $params )
    {
        if ( empty($params['groupId']) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            if(!isset($params['code'])){
                throw new Redirect404Exception();
            }
            $code = $params['code'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'declineInvite_group')));
        }

        $groupId = (int) $params['groupId'];
        $userId = OW::getUser()->getId();

        $invite = $this->service->findInvite($groupId, $userId);
        $inviteId = $invite->id;
        $status = 'ignored';

        GROUPS_BOL_Service::getInstance()->deleteInvite($groupId, $userId);

        $redirectUrl = OW::getRouter()->urlForRoute('groups-invite-list');
        OW::getFeedback()->info(OW::getLanguage()->text('groups', 'invite_declined_message'));

        $this->redirect($redirectUrl);
    }

    public function leave( $params )
    {
        if ( empty($params['groupId']) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $groupId = (int) $params['groupId'];
        $userId = OW::getUser()->getId();

        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            if(!isset($_GET['code'])){
                throw new Redirect404Exception();
            }
            $code =$_GET['code'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => $userId, 'code'=>$code,'activityType'=>'leave_group')));
        }
        $userIds = array($userId);
        $result = GROUPS_BOL_Service::getInstance()->deleteUser($groupId, $userIds);
        if($result) {
            $redirectUrl = OW::getRouter()->urlForRoute('groups-my-list');
            OW::getFeedback()->info(OW::getLanguage()->text('groups', 'leave_complete_message'));
        }else{
            $redirectUrl = OW::getRouter()->urlForRoute('groups-view', array('groupId' => $groupId));
            OW::getFeedback()->warning(OW::getLanguage()->text('groups', 'leave_fail_message'));
        }

        $this->redirect($redirectUrl);
    }

    public function deleteUser( $params )
    {
        if ( empty($params['groupId']) || empty($params['userId']) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            if(!isset($_GET['code'])){
                throw new Redirect404Exception();
            }
            $code = $_GET['code'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'deleteUser_group')));
        }
        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($params['groupId']);

        if ( $groupDto === null )
        {
            throw new Redirect404Exception();
        }

        if (!OW::getUser()->isAdmin()) {
            $isModerator = OW::getUser()->isAuthorized('groups');
            $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.check.user.manager.status', array('groupId'=>$params['groupId']));
            OW::getEventManager()->trigger($eventIisGroupsPlusManager);
            if(isset($eventIisGroupsPlusManager->getData()['isUserManager'])){
                $isModerator=$eventIisGroupsPlusManager->getData()['isUserManager'];
            }
            if ( !$isModerator && $groupDto->userId != OW::getUser()->getId()  )
            {
                throw new Redirect404Exception();
            }
        }

        $groupId = (int) $groupDto->id;
        $userIds = array($params['userId']);
        $result = GROUPS_BOL_Service::getInstance()->deleteUser($groupId, $userIds);
        if($result) {
            $redirectUrl = OW::getRouter()->urlForRoute('groups-my-list');
            if(isset($_GET['redirectUri']) && !empty($_GET['redirectUri'])) {
                $redirectUrl = OW_URL_HOME . urldecode($_GET['redirectUri']);
            }
            OW::getFeedback()->info(OW::getLanguage()->text('groups', 'delete_user_success_message'));
        }else{
            $redirectUrl = OW::getRouter()->urlForRoute('groups-view', array('groupId' => $groupId));
            OW::getFeedback()->warning(OW::getLanguage()->text('groups', 'delete_user_fail_message'));
        }

        $this->redirect($redirectUrl);
    }

    private function getPaging( $page, $perPage, $onPage )
    {
        $paging['page'] = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $paging['perPage'] = $perPage;

        $paging['first'] = ($paging['perPage'] - 1) * $paging['perPage'];
        $paging['count'] = $paging['perPage'];
    }

    public function mostPopularList()
    {
        $language = OW::getLanguage();

        OW::getDocument()->setHeading($language->text('groups', 'group_list_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_files');

        OW::getDocument()->setTitle($language->text('groups', 'popular_list_page_title'));
        OW::getDocument()->setDescription($language->text('groups', 'popular_list_page_description'));

        if ( !$this->service->isCurrentUserCanViewList() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'view');
            throw new AuthorizationException($status['msg']);
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 20;
        $first = ($page - 1) * $perPage;
        $count = $perPage;
        $originalUrl = $url = OW::getRouter()->urlForRoute('groups-most-popular');
        $this->assign('originalUrl',$originalUrl);
        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::GET_RESULT_FOR_LIST_ITEM_GROUP, array('popular'=>true,
            'groupController' => $this,'first'=>$first,'count'=>$count,'status'=>'active','page'=>$page,'perPage'=>$perPage,'url'=>$url)));
        if(isset($resultsEvent->getData()['groups']) && isset($resultsEvent->getData()['groupsCount'])) {
            $dtoList = $resultsEvent->getData()['groups'];
            $listCount = $resultsEvent->getData()['groupsCount'];
            $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);
            if(isset($resultsEvent->getData()['page']) && $resultsEvent->getData()['page']!=null){
                $page = $resultsEvent->getData()['page'];
                $extraParams = array();
                if(isset($resultsEvent->getData()['searchTitle']))
                {
                    $extraParams['searchTitle'] = $resultsEvent->getData()['searchTitle'];
                }
                if(isset($resultsEvent->getData()['categoryStatus']))
                {
                    $extraParams['categoryStatus'] = $resultsEvent->getData()['categoryStatus'];
                }
                if(isset($resultsEvent->getData()['status']))
                {
                    $extraParams['status'] = $resultsEvent->getData()['status'];
                }
                if(sizeof($extraParams)>0){
                    $url = OW::getRequest()->buildUrlQueryString($url,$extraParams);
                }
                $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5,"", $url);
            }
        }
        else {
            $dtoList = $this->service->findGroupList(GROUPS_BOL_Service::LIST_MOST_POPULAR, $first, $count);
            $listCount = $this->service->findGroupListCount(GROUPS_BOL_Service::LIST_MOST_POPULAR);
            $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);
        }

        $menu = $this->getGroupListMenu();
        $menu->setItemActive('popular');
        $this->assign('listType', 'popular');

        $this->displayGroupList($dtoList, $paging, $menu);

        $params = array(
            "sectionKey" => "groups",
            "entityKey" => "mostPopular",
            "title" => "groups+meta_title_most_popular",
            "description" => "groups+meta_desc_most_popular",
            "keywords" => "groups+meta_keywords_most_popular"
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }

    public function latestList()
    {
        $language = OW::getLanguage();

        OW::getDocument()->setHeading($language->text('groups', 'group_list_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_files');

        OW::getDocument()->setTitle($language->text('groups', 'latest_list_page_title'));
        OW::getDocument()->setDescription($language->text('groups', 'latest_list_page_description'));

        if ( !$this->service->isCurrentUserCanViewList() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'view');
            throw new AuthorizationException($status['msg']);
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 20;
        $first = ($page - 1) * $perPage;
        $count = $perPage;
        $originalUrl = $url = OW::getRouter()->urlForRoute('groups-latest');
        $this->assign('originalUrl',$originalUrl);
        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::GET_RESULT_FOR_LIST_ITEM_GROUP, array('latest'=>true,
            'groupController' => $this,'first'=>$first,'count'=>$count,'status'=>'active','page'=>$page,'perPage'=>$perPage,'url'=>$url)));
        if(isset($resultsEvent->getData()['groups']) && isset($resultsEvent->getData()['groupsCount'])) {
            $dtoList = $resultsEvent->getData()['groups'];
            $listCount = $resultsEvent->getData()['groupsCount'];
            $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);
            if(isset($resultsEvent->getData()['page']) && $resultsEvent->getData()['page']!=null){
                $page = $resultsEvent->getData()['page'];
                $extraParams = array();
                if(isset($resultsEvent->getData()['searchTitle']))
                {
                    $extraParams['searchTitle'] = $resultsEvent->getData()['searchTitle'];
                }
                if(isset($resultsEvent->getData()['categoryStatus']))
                {
                    $extraParams['categoryStatus'] = $resultsEvent->getData()['categoryStatus'];
                }
                if(isset($resultsEvent->getData()['status']))
                {
                    $extraParams['status'] = $resultsEvent->getData()['status'];
                }
                if(sizeof($extraParams)>0){
                    $url = OW::getRequest()->buildUrlQueryString($url,$extraParams);
                }
                $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5,"", $url);
            }
        }
        else {
            $dtoList = $this->service->findGroupList(GROUPS_BOL_Service::LIST_LATEST, $first, $count);
            $listCount = $this->service->findGroupListCount(GROUPS_BOL_Service::LIST_LATEST);
            $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);
        }


        $menu = $this->getGroupListMenu();
        $menu->setItemActive('latest');
        $this->assign('listType', 'latest');

        $this->displayGroupList($dtoList, $paging, $menu);

        $params = array(
            "sectionKey" => "groups",
            "entityKey" => "latest",
            "title" => "groups+meta_title_latest",
            "description" => "groups+meta_desc_latest",
            "keywords" => "groups+meta_keywords_latest"
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }

    public function inviteList()
    {
        $userId = OW::getUser()->getId();

        if ( empty($userId) )
        {
            throw new AuthenticateException();
        }

        $language = OW::getLanguage();

        OW::getDocument()->setHeading($language->text('groups', 'group_list_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_files');

        OW::getDocument()->setTitle($language->text('groups', 'invite_list_page_title'));

        if ( !$this->service->isCurrentUserCanViewList() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'view');
            throw new AuthorizationException($status['msg']);
        }

        OW::getRegistry()->set('groups.hide_console_invite_item', true);

        $this->service->markAllInvitesAsViewed($userId);

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 20;
        $first = ($page - 1) * $perPage;
        $count = $perPage;

        $dtoList = $this->service->findInvitedGroups($userId, $first, $count);
        $listCount = $this->service->findInvitedGroupsCount($userId);

        $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);

        $menu = $this->getGroupListMenu();
        $menu->setItemActive('invite');
        $this->assign('listType', 'invite');

        $templatePath = OW::getPluginManager()->getPlugin('groups')->getCtrlViewDir() . 'groups_list.html';

        $this->setTemplate($templatePath);

        $acceptUrls = array();
        $declineUrls = array();

        $out = array();

        foreach ( $dtoList as $group )
        {
            $acceptUrls[$group->id] = OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'join', array(
                'groupId' => $group->id
            ));
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$group->id,'isPermanent'=>true,'activityType'=>'join_group')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $code = $frmSecuritymanagerEvent->getData()['code'];
                $acceptUrls[$group->id] = OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'join', array(
                    'groupId' => $group->id,
                    'code'=>$code
                ));
            }


            $declineUrls[$group->id] = OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'declineInvite', array(
                'groupId' => $group->id
            ));

            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$group->id,'isPermanent'=>true,'activityType'=>'declineInvite_group')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $code = $frmSecuritymanagerEvent->getData()['code'];
                $declineUrls[$group->id] = OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'declineInvite', array(
                    'groupId' => $group->id,
                    'code'=>$code
                ));
            }
        }

        $acceptLabel = OW::getLanguage()->text('groups', 'invite_accept_label');
        $declineLabel = OW::getLanguage()->text('groups', 'invite_decline_label');

        foreach ( $dtoList as $item )
        {
            /* @var $item GROUPS_BOL_Group */

            $userCount = GROUPS_BOL_Service::getInstance()->findUserListCount($item->id);
            $title = strip_tags($item->title);

            $toolbar = array(
                array(
                    'label' => OW::getLanguage()->text('groups', 'listing_users_label', array(
                        'count' => $userCount
                    ))
                ),

                array(
                    'href' => $acceptUrls[$item->id],
                    'label' => $acceptLabel
                ),

                array(
                    'href' => $declineUrls[$item->id],
                    'label' => $declineLabel
                )
            );

            $out[] = array(
                'id' => $item->id,
                'url' => OW::getRouter()->urlForRoute('groups-view', array('groupId' => $item->id)),
                'title' => $title,
                'imageTitle' => $title,
                'content' => strip_tags($item->description),
                'time' => UTIL_DateTime::formatDate($item->timeStamp),
                'imageSrc' => GROUPS_BOL_Service::getInstance()->getGroupImageUrl($item),
                'users' => $userCount,
                'toolbar' => $toolbar
            );
        }

        $this->addComponent('paging', $paging);

        if ( !empty($menu) )
        {
            $this->addComponent('menu', $menu);
        }
        else
        {
            $this->assign('menu', '');
        }
        $this->assign("showCreate", true);
        if ( !$this->service->isCurrentUserCanCreate() )
        {
            $authStatus = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'create');
            if ( $authStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $this->assign("authMsg", json_encode($authStatus["msg"]));
            }
            else 
            {
                $this->assign("showCreate", false);
            }
        }

        $this->assign('list', $out);
    }


    public function myGroupList()
    {
        $userId = OW::getUser()->getId();

        if ( empty($userId) )
        {
            throw new AuthenticateException();
        }

        $language = OW::getLanguage();

        OW::getDocument()->setHeading($language->text('groups', 'group_list_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_files');

        OW::getDocument()->setTitle($language->text('groups', 'my_list_page_title'));

        if ( !$this->service->isCurrentUserCanViewList() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'view');
            throw new AuthorizationException($status['msg']);
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 20;
        $first = ($page - 1) * $perPage;
        $count = $perPage;
        $originalUrl =$url= OW::getRouter()->urlForRoute('groups-my-list');
        $this->assign('originalUrl',$originalUrl);
        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::GET_RESULT_FOR_LIST_ITEM_GROUP, array('userId'=>$userId,
            'groupController' => $this,'first'=>$first,'count'=>$count,'status'=>'active',
            'page'=>$page,'perPage'=>$perPage,'url'=>$url, 'type'=>GROUPS_BOL_GroupDao::GROUP)));
        if(isset($resultsEvent->getData()['groups']) && isset($resultsEvent->getData()['groupsCount'])) {
            $dtoList = $resultsEvent->getData()['groups'];
            $listCount = $resultsEvent->getData()['groupsCount'];
            $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);
            if(isset($resultsEvent->getData()['page']) && $resultsEvent->getData()['page']!=null){
                $page = $resultsEvent->getData()['page'];
                $extraParams = array();
                if(isset($resultsEvent->getData()['searchTitle']))
                {
                    $extraParams['searchTitle'] = $resultsEvent->getData()['searchTitle'];
                }
                if(isset($resultsEvent->getData()['categoryStatus']))
                {
                    $extraParams['categoryStatus'] = $resultsEvent->getData()['categoryStatus'];
                }
                if(isset($resultsEvent->getData()['status']))
                {
                    $extraParams['status'] = $resultsEvent->getData()['status'];
                }
                if(sizeof($extraParams)>0){
                    $url = OW::getRequest()->buildUrlQueryString($url,$extraParams);
                }
                $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5,"", $url);
            }
        }
        else {
            $dtoList = $this->service->findMyGroups($userId, $first, $count, GROUPS_BOL_GroupDao::GROUP);
            $listCount = $this->service->findMyGroupsCount($userId);
            $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);
        }

        $menu = $this->getGroupListMenu();
        $menu->setItemActive('my');
        $this->assign('listType', 'my');

        $this->displayGroupList($dtoList, $paging, $menu);
    }

    public function myChannelList()
    {
        $userId = OW::getUser()->getId();

        if ( empty($userId) )
        {
            throw new AuthenticateException();
        }

        $language = OW::getLanguage();

        OW::getDocument()->setHeading($language->text('groups', 'group_list_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_files');

        OW::getDocument()->setTitle($language->text('groups', 'my_list_page_title'));

        if ( !$this->service->isCurrentUserCanViewList() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'view');
            throw new AuthorizationException($status['msg']);
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 20;
        $first = ($page - 1) * $perPage;
        $count = $perPage;
        $originalUrl =$url= OW::getRouter()->urlForRoute('groups-my-list');
        $this->assign('originalUrl',$originalUrl);

        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::GET_RESULT_FOR_LIST_ITEM_GROUP, array('userId'=>$userId,
            'groupController' => $this,'first'=>$first,'count'=>$count,'status'=>'active',
            'page'=>$page,'perPage'=>$perPage,'url'=>$url, 'type'=>GROUPS_BOL_GroupDao::CHANEL)));
        if(isset($resultsEvent->getData()['groups']) && isset($resultsEvent->getData()['groupsCount'])) {
            $dtoList = $resultsEvent->getData()['groups'];
            $listCount = $resultsEvent->getData()['groupsCount'];
            $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);
            if(isset($resultsEvent->getData()['page']) && $resultsEvent->getData()['page']!=null){
                $page = $resultsEvent->getData()['page'];
                $extraParams = array();
                if(isset($resultsEvent->getData()['searchTitle']))
                {
                    $extraParams['searchTitle'] = $resultsEvent->getData()['searchTitle'];
                }
                if(isset($resultsEvent->getData()['categoryStatus']))
                {
                    $extraParams['categoryStatus'] = $resultsEvent->getData()['categoryStatus'];
                }
                if(isset($resultsEvent->getData()['status']))
                {
                    $extraParams['status'] = $resultsEvent->getData()['status'];
                }
                if(sizeof($extraParams)>0){
                    $url = OW::getRequest()->buildUrlQueryString($url,$extraParams);
                }
                $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5,"", $url);
            }
        }
        else {
            $dtoList = $this->service->findMyGroups($userId, $first, $count, GROUPS_BOL_GroupDao::CHANEL);
            $listCount = $this->service->findMyGroupsCount($userId);
            $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);
        }

        $menu = $this->getGroupListMenu();
        $menu->setItemActive('my-channels');
        $this->assign('listType', 'my-channels');

        $this->displayGroupList($dtoList, $paging, $menu);
    }

    public function userGroupList( $params )
    {
        $userDto = BOL_UserService::getInstance()->findByUsername(trim($params['user']));

        if ( empty($userDto) )
        {
            throw new Redirect404Exception();
        }

        $originalUrl = $url = OW::getRouter()->urlForRoute('groups-user-groups', array('user'=>$params['user']));
        $this->assign('originalUrl',$originalUrl);

        // privacy check
        $userId = $userDto->id;
        $viewerId = OW::getUser()->getId();
        $ownerMode = $userId == $viewerId;
        $modPermissions = OW::getUser()->isAuthorized('groups');

        if ( !$ownerMode && !$modPermissions )
        {
            $privacyParams = array('action' => GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS, 'ownerId' => $userId, 'viewerId' => $viewerId);
            $event = new OW_Event('privacy_check_permission', $privacyParams);

            OW::getEventManager()->trigger($event);
        }

        $language = OW::getLanguage();
        OW::getDocument()->setTitle($language->text('groups', 'user_groups_page_title'));
        OW::getDocument()->setDescription($language->text('groups', 'user_groups_page_description'));
        OW::getDocument()->setHeading($language->text('groups', 'user_group_list_heading', array(
                'userName' => BOL_UserService::getInstance()->getDisplayName($userDto->id)
            )));

        OW::getDocument()->setHeadingIconClass('ow_ic_files');

        if ( !$this->service->isCurrentUserCanViewList() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'view');
            throw new AuthorizationException($status['msg']);
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 21;
        $first = ($page - 1) * $perPage;
        $count = $perPage;
        $url = OW::getRouter()->urlForRoute('groups-user-groups',array('user'=>$params['user']));
        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::GET_RESULT_FOR_LIST_ITEM_GROUP, array('userId'=>$userDto->id,
            'groupController' => $this,'first'=>$first,'count'=>$count,'status'=>'active','page'=>$page,'perPage'=>$perPage,'url'=>$url)));
        if(isset($resultsEvent->getData()['groups']) && isset($resultsEvent->getData()['groupsCount'])) {
            $dtoList = $resultsEvent->getData()['groups'];
            $listCount = $resultsEvent->getData()['groupsCount'];
            $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);
            if(isset($resultsEvent->getData()['page']) && $resultsEvent->getData()['page']!=null){
                $page = $resultsEvent->getData()['page'];
                $extraParams = array();
                if(isset($resultsEvent->getData()['searchTitle']))
                {
                    $extraParams['searchTitle'] = $resultsEvent->getData()['searchTitle'];
                }
                if(isset($resultsEvent->getData()['categoryStatus']))
                {
                    $extraParams['categoryStatus'] = $resultsEvent->getData()['categoryStatus'];
                }
                if(isset($resultsEvent->getData()['status']))
                {
                    $extraParams['status'] = $resultsEvent->getData()['status'];
                }
                if(sizeof($extraParams)>0){
                    $url = OW::getRequest()->buildUrlQueryString($url,$extraParams);
                }
                $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5,"", $url);
            }
            $correctedTitle= true;
            OW::getDocument()->setTitle($language->text('groups', 'user_group_list_heading', array(
                'userName' => BOL_UserService::getInstance()->getDisplayName($userDto->id)
            )));
        }
        else {
            $dtoList = $this->service->findUserGroupList($userDto->id, $first, $count);
            $listCount = $this->service->findUserGroupListCount($userDto->id);
            $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);
        }

        $this->assign('hideCreateNew', true);
        
        $this->assign('listType', 'user');

        $this->displayGroupList($dtoList, $paging);

        $vars = BOL_SeoService::getInstance()->getUserMetaInfo($userDto);
        if(!$correctedTitle) {
            $params = array(
                "sectionKey" => "groups",
                "entityKey" => "userGroups",
                "title" => "groups+meta_title_user_groups",
                "description" => "groups+meta_desc_user_groups",
                "keywords" => "groups+meta_keywords_user_groups",
                "vars" => $vars
            );

            OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
        }
    }

    private function displayGroupList( $list, $paging, $menu = null )
    {
        $templatePath = OW::getPluginManager()->getPlugin('groups')->getCtrlViewDir() . 'groups_list.html';
        $this->setTemplate($templatePath);

        $out = array();
        $groupIdList = array();
        foreach ( $list as $item ) {
            $groupIdList[] = $item->id;
        }
        $userCountList = $this->service->findUserCountForList($groupIdList);

        foreach ( $list as $item )
        {
            /* @var $item GROUPS_BOL_Group */

            $userCount = 0;
            if (isset($userCountList[$item->id])) {
                $userCount = (int) $userCountList[$item->id];
            }
            $title = strip_tags($item->title);
            $eventPrepareGroup = OW::getEventManager()->trigger(new OW_Event('on.prepare.group.data',['parentGroupId'=>isset($item->parentGroupId)? $item->parentGroupId : null]));
            $parentTitle=null;
            if(isset($eventPrepareGroup->getData()['parentData'])){
                $parentTitle = $eventPrepareGroup->getData()['parentData'];
            }
            $toolbar = array(
                array(
                    'label' => OW::getLanguage()->text('groups', 'listing_users_label', array(
                        'count' => $userCount
                    ))
                )
            );
            $sentenceCorrected = false;
            if ( mb_strlen($item->description) > 300 )
            {
                $sentence = strip_tags($item->description);
                $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_HALF_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 300)));
                if(isset($event->getData()['correctedSentence'])){
                    $sentence = $event->getData()['correctedSentence'];
                    $sentenceCorrected = true;
                }
                $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 300)));
                if(isset($event->getData()['correctedSentence'])){
                    $sentence = $event->getData()['correctedSentence'];
                    $sentenceCorrected = true;
                }
            }
            if($sentenceCorrected){
                $content = $sentence.'...';
            }
            else{
                $content = UTIL_String::truncate(strip_tags($item->description), 300, "...");
            }
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $content)));
            if (isset($stringRenderer->getData()['string'])) {
                $content = ($stringRenderer->getData()['string']);
            }
            $out[] = array(
                'id' => $item->id,
                'url' => OW::getRouter()->urlForRoute('groups-view', array('groupId' => $item->id)),
                'title' => $title,
                'imageTitle' => $title,
                'content' => $content,
                'time' => UTIL_DateTime::formatDate($item->timeStamp),
                'imageSrc' => GROUPS_BOL_Service::getInstance()->getGroupImageUrl($item),
                'users' => $userCount,
                'toolbar' => $toolbar,
                'unreadCount' => GROUPS_BOL_Service::getInstance()->getUnreadCountForGroupUser($item->id),
                'parentTitle' =>$parentTitle
            );
            $last_out = &$out[sizeof($out) - 1];
            $last_out['imageInfo'] = BOL_AvatarService::getInstance()->getAvatarInfo($last_out['id'], $last_out['imageSrc'], 'group');
        }

        $this->addComponent('paging', $paging);

        if ( !empty($menu) )
        {
            $this->addComponent('menu', $menu);
        }
        else
        {
            $this->assign('menu', '');
        }

        $this->assign("showCreate", true);
        
        if ( !$this->service->isCurrentUserCanCreate() )
        {
            $authStatus = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'create');
            if ( $authStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $this->assign("authMsg", json_encode($authStatus["msg"]));
            }
            else 
            {
                $this->assign("showCreate", false);
            }
        }
        
        $this->assign('list', $out);
        $this->setDocumentKey("user_groups");
    }

    public function userList( $params )
    {
        $groupId = (int) $params['groupId'];
        $groupDto = $this->service->findGroupById($groupId);

        $this->setPageHeading(OW::getLanguage()->text('groups', 'group_members'));
        $userCount = GROUPS_BOL_Service::getInstance()->findUserListCount($groupId);

        if ( $groupDto === null )
        {
            throw new Redirect404Exception();
        }

        if ( !$this->service->isCurrentUserCanView($groupDto, true) )
        {
            throw new Redirect404Exception();
        }

        if ( $groupDto->whoCanView == GROUPS_BOL_Service::WCV_INVITE && !OW::getUser()->isAuthorized('groups') )
        {
            if ( !OW::getUser()->isAuthenticated() )
            {
                throw new Redirect404Exception();
            }

            $invite = $this->service->findInvite($groupDto->id, OW::getUser()->getId());
            $user = $this->service->findUser($groupDto->id, OW::getUser()->getId());

            if ( $groupDto->whoCanView == GROUPS_BOL_Service::WCV_INVITE && $user === null )
            {
                if( $invite === null ) {
                    throw new Redirect404Exception();
                }else{
                    $this->redirect(OW::getRouter()->urlForRoute('groups-invite-list'));
                }
            }
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 21;
        $first = ($page - 1) * $perPage;
        $count = $perPage;

        $event = new OW_Event('groups.member_list_page_render',array('groupDto'=>$groupDto));
        OW_EventManager::getInstance()->trigger($event);

        $extraComponents = $event->getData();
        if(isset($extraComponents) && is_array($extraComponents)){
            $this->assign('extraComponent',$extraComponents);
            foreach ($extraComponents as $extraComponent){
                $this->addComponent($extraComponent['name'],$extraComponent['component']);
            }
        }else{
            $this->assign('extraComponent',array());
        }


        if( GROUPS_CMP_BriefInfoWidget::userAllowedAccess() ){
            $this->addComponent('groupBriefInfo', new GROUPS_CMP_BriefInfo($groupId));
        }

        $searchForm = $this->service->getUserSearchForm();
        $url = OW::getRouter()->urlForRoute('groups-user-list', array('groupId' => $groupId));
        $this->assign('groupUserUrl', $url);
        if(isset($_GET['searchValue'])) {
            $searchValue = $_GET['searchValue'];
            $searchValue = UTIL_HtmlTag::stripTagsAndJs($searchValue);
            $searchForm->getElement('searchValue')->setValue($searchValue);
            $this->assign('searchValue', $searchValue);
            $this->assign('groupUserUrl', $url);
            $listCount = $this->service->findUserListCountBySearch($groupId, $searchValue);
            $dtoList = $this->service->findUserListBySearch($groupId, $first, $count, $searchValue);
        }else {
        $listCount = $this->service->findUserListCount($groupId);
        $dtoList = $this->service->findUserList($groupId, $first, $count);
        }
        $listCmp = new GROUPS_UserList($groupDto, $dtoList, $listCount, $perPage);
        $this->addComponent('listCmp', $listCmp);


       $this->addForm($searchForm);

        $this->assign("groupId", $groupId);
        $this->assign("userCount", $userCount);

        $params = array(
            "sectionKey" => "groups",
            "entityKey" => "groupUsers",
            "title" => "groups+meta_title_group_users",
            "description" => "groups+meta_desc_group_users",
            "keywords" => "groups+meta_keywords_group_users",
            "vars" => array( "group_title" => $groupDto->title )
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }

    private function getGroupListMenu()
    {

        $language = OW::getLanguage();

        $items = array();

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('groups', 'group_list_menu_item_popular'))
            ->setKey('popular')
            ->setUrl(OW::getRouter()->urlForRoute('groups-most-popular'))
            ->setOrder(4)
            ->setIconClass('ow_ic_most_popular ow_dynamic_color_icon');
        $items[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('groups', 'group_list_menu_item_latest'))
            ->setKey('latest')
            ->setUrl(OW::getRouter()->urlForRoute('groups-latest'))
            ->setOrder(5)
            ->setIconClass('ow_ic_latest ow_dynamic_color_icon');
        $items[] = $item;


        if ( OW::getUser()->isAuthenticated() )
        {
            $item = new BASE_MenuItem();
            $item->setLabel($language->text('groups', 'group_list_menu_item_my'))
                ->setKey('my')
                ->setUrl(OW::getRouter()->urlForRoute('groups-my-list'))
                ->setOrder(1)
                ->setIconClass('ow_ic_my_groups ow_dynamic_color_icon');
            $items[] = $item;

            $item = new BASE_MenuItem();
            $item->setLabel($language->text('groups', 'group_list_menu_item_my_channels'))
                ->setKey('my-channels')
                ->setUrl(OW::getRouter()->urlForRoute('groups-my-list-channels'))
                ->setOrder(2)
                ->setIconClass('ow_ic_tag ow_ic_my_channels ow_dynamic_color_icon');
            $items[] = $item;

            $item = new BASE_MenuItem();
            $item->setLabel($language->text('groups', 'group_list_menu_item_invite'))
                ->setKey('invite')
                ->setUrl(OW::getRouter()->urlForRoute('groups-invite-list'))
                ->setOrder(10)
                ->setIconClass('ow_ic_invite ow_dynamic_color_icon');
            $items[] = $item;
        }

        return new BASE_CMP_ContentMenu($items);
    }

    public function follow()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $groupId = (int) $_GET['groupId'];

        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);

        if ( $groupDto === null )
        {
            throw new Redirect404Exception();
        }

        if ($groupDto->whoCanView == GROUPS_BOL_Service::WCV_INVITE) {
            $userDtoInGroup = $this->service->findUser($groupId, OW::getUser()->getId());
            if ($userDtoInGroup == null) {
                throw new Redirect404Exception();
            }
        }

        $eventParams = array(
            'userId' => OW::getUser()->getId(),
            'feedType' => GROUPS_BOL_Service::ENTITY_TYPE_GROUP,
            'feedId' => $groupId
        );

        $title = UTIL_String::truncate(strip_tags($groupDto->title), 100, '...');

        switch ( $_GET['command'] )
        {
            case 'follow':
                if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
                    if(!isset($_GET['code'])){
                        throw new Redirect404Exception();
                    }
                    $code = $_GET['code'];
                    OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                        array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'follow_group')));
                }
                OW::getEventManager()->call('feed.add_follow', $eventParams);
                OW::getFeedback()->info(OW::getLanguage()->text('groups', 'feed_follow_complete_msg'));
                break;

            case 'unfollow':
                if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
                    if(!isset($_GET['code'])){
                        throw new Redirect404Exception();
                    }
                    $code = $_GET['code'];
                    OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                        array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'unFollow_group')));
                }
                OW::getEventManager()->call('feed.remove_follow', $eventParams);
                OW::getFeedback()->info(OW::getLanguage()->text('groups', 'feed_unfollow_complete_msg'));
                break;
        }

        $this->redirect(OW_URL_HOME . htmlspecialchars($_GET['backUri'], ENT_QUOTES, 'UTF-8'));
    }

    public function invite()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $userId = OW::getUser()->getId();

        if ( empty($userId) )
        {
            throw new AuthenticateException();
        }

        $respoce = array();

        $userIds = json_decode($_POST['userIdList']);
        $groupId = $_POST['groupId'];
        $allIdList = json_decode($_POST['allIdList']);

        $group = $this->service->findGroupById($groupId);

        if(!OW::getUser()->isAdmin()){
            if(!$this->service->isCurrentUserInvite($group->id)) {
                $respondArray['messageType'] = 'error';
                $respondArray['message'] = OW::getLanguage()->text('groups', 'invitation_access_denied');
                $respondArray['result'] = false;
                $respondArray['error'] = 'error';
                exit(json_encode($respondArray));
            }
        }

        $count = 0;
        $eventIisGroupsPlusCheckCanSearchAll = new OW_Event('frmgroupsplus.check.can.invite.all',array('checkAccess'=>true));
        OW::getEventManager()->trigger($eventIisGroupsPlusCheckCanSearchAll);
        if(isset($eventIisGroupsPlusCheckCanSearchAll->getData()['hasAccess'])){
            $hasAccess=true;
        }
        if(isset($eventIisGroupsPlusCheckCanSearchAll->getData()['directInvite']) && $eventIisGroupsPlusCheckCanSearchAll->getData()['directInvite']==true){
            $eventIisGroupsPlusAddAutomatically = new OW_Event('frmgroupsplus.add.users.automatically',array('groupId'=>$groupId,'userIds'=>$userIds));
            OW::getEventManager()->trigger($eventIisGroupsPlusAddAutomatically);
            $respoce['directAdd'] = true;
            $respoce['url'] = $this->service->getGroupUrl($group);
            exit(json_encode($respoce));
        }else {
            foreach ($userIds as $uid) {
                if ($userId == $uid)
                    continue;
                if (isset($hasAccess)) {
                    if($this->service->inviteUser($group->id, $uid, $userId)){
                        $count++;
                    }
                    continue;
                }
                $isFriends = FRIENDS_BOL_Service::getInstance()->findFriendship($userId, $uid);
                if (isset($isFriends) && $isFriends->status == 'active') {
                    if($this->service->inviteUser($group->id, $uid, $userId)){
                        $count++;
                    }
                }
            }

            $respoce['directAdd'] = false;
            $respoce['messageType'] = 'info';
            $respoce['message'] = OW::getLanguage()->text('groups', 'users_invite_success_message', array('count' => $count));
            $respoce['allIdList'] = array_diff($allIdList, $userIds);

            exit(json_encode($respoce));
        }
    }

    public function privateGroup( $params )
    {
        $language = OW::getLanguage();

        $this->setPageTitle($language->text('groups', 'private_page_title'));
        $this->setPageHeading($language->text('groups', 'private_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_lock');

        $groupId = $params['groupId'];
        $group = $this->service->findGroupById($groupId);

        $avatarList = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($group->userId));
        $displayName = BOL_UserService::getInstance()->getDisplayName($group->userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($group->userId);

        $this->assign('group', $group);
        $this->assign('avatar', $avatarList[$group->userId]);
        $this->assign('displayName', $displayName);
        $this->assign('userUrl', $userUrl);
        $this->assign('creator', $language->text('groups', 'creator'));
    }
    
    public function approve( $params )
    {
        $entityId = $params["groupId"];
        $entityType = GROUPS_CLASS_ContentProvider::ENTITY_TYPE;
        
        $backUrl = OW::getRouter()->urlForRoute("groups-view", array(
            "groupId" => $entityId
        ));
        
        $event = new OW_Event("moderation.approve", array(
            "entityType" => $entityType,
            "entityId" => $entityId
        ));
        
        OW::getEventManager()->trigger($event);
        
        $data = $event->getData();
        if ( empty($data) )
        {
            $this->redirect($backUrl);
        }
        
        if ( $data["message"] )
        {
            OW::getFeedback()->info($data["message"]);
        }
        else
        {
            OW::getFeedback()->error($data["error"]);
        }
        
        $this->redirect($backUrl);
    }

    public function setUserAsOwner( $params ){
        if ( empty($params['groupId']) || empty($params['userId']) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($params['groupId']);

        if ( $groupDto === null )
        {
            throw new Redirect404Exception();
        }
        if (!GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($groupDto))
        {
            throw new Redirect404Exception();
        }

        $groupId = (int) $groupDto->id;
        $userId = $params['userId'];

        $this->service->setGroupOwner($groupId, $userId);

        OW::getFeedback()->info(OW::getLanguage()->text('groups', 'set_user_as_owner_success_message'));

        $redirectUri = urldecode($_GET['redirectUri']);
        $this->redirect(OW_URL_HOME . $redirectUri);
    }
}

// Additional calsses

class GROUPS_UserList extends BASE_CMP_Users
{
    /**
     *
     * @var GROUPS_BOL_Group
     */
    protected $groupDto;
    protected $groupManager;
    protected $managerIds;

    public function __construct( GROUPS_BOL_Group $groupDto, $list, $itemCount, $usersOnPage, $showOnline = true)
    {
        parent::__construct($list, $itemCount, $usersOnPage, $showOnline);
        $this->managerIds = array();
        $this->groupDto = $groupDto;
        $this->groupManager = false;
        $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.check.user.manager.status', array('groupId'=>$this->groupDto->getId(), 'all_manager_ids' => true));
        OW::getEventManager()->trigger($eventIisGroupsPlusManager);
        if(isset($eventIisGroupsPlusManager->getData()['managerIds'])){
            $this->managerIds = $eventIisGroupsPlusManager->getData()['managerIds'];
            $this->groupManager = in_array(OW::getUser()->getId(), $this->managerIds);
        }
    }

    public function getContextMenu($userId, $additionalInfo = array())
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return null;
        }

        $isOwner = $this->groupDto->userId == OW::getUser()->getId();
        $contextActionMenu = new BASE_CMP_ContextAction();

        $contextParentAction = new BASE_ContextAction();
        $contextParentAction->setKey('group_user_' . $userId);
        $contextActionMenu->addAction($contextParentAction);


        $groupModerator = OW::getUser()->isAuthorized('groups');
        $groupModerator = $groupModerator || $this->groupManager;

        if ( ($isOwner || $groupModerator) && ($groupModerator || $userId != OW::getUser()->getId()) && $userId != $this->groupDto->userId)
        {
            $contextAction = new BASE_ContextAction();
            $contextAction->setParentKey($contextParentAction->getKey());
            $contextAction->setKey('delete_group_user');
            $contextAction->setLabel(OW::getLanguage()->text('groups', 'delete_group_user_label'));

            if ( $this->groupDto->userId != $userId )
            {
                $callbackUri = OW::getRequest()->getRequestUri();
                $urlParams = array(
                    'redirectUri' => urlencode($callbackUri)
                );
                $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                    array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'deleteUser_group')));
                if(isset($frmSecuritymanagerEvent->getData()['code'])){
                    $urlParams['code'] = $frmSecuritymanagerEvent->getData()['code'];

                }
                $deleteUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'deleteUser', array(
                    'groupId' => $this->groupDto->id,
                    'userId' => $userId
                )),$urlParams );

                $contextAction->setUrl($deleteUrl);

                $contextAction->addAttribute('data-message', OW::getLanguage()->text('groups', 'delete_group_user_confirmation'));
                $contextAction->addAttribute('onclick', "return confirm_redirect($(this).data().message, '{$deleteUrl}');");
                $contextAction->addAttribute('class', "delete_user_from_group_icon");

            }
            else
            {
                $contextAction->setUrl('javascript://');
                $contextAction->addAttribute('data-message', OW::getLanguage()->text('groups', 'group_owner_delete_error'));
                $contextAction->addAttribute('onclick', "OW.error($(this).data().message); return false;");
            }

            $contextActionMenu->addAction($contextAction);
            $eventIisGroupsplus = new OW_Event('frmgroupsplus.set.user.manager.status', array('contextParentActionKey'=>$contextParentAction->getKey(),
                'userId'=>$userId,'groupOwnerId'=>$this->groupDto->userId,'groupId'=>$this->groupDto->id,'contextActionMenu'=>$contextActionMenu, 'managerIds' => $this->managerIds));
            OW::getEventManager()->trigger($eventIisGroupsplus);
            $event = new OW_Event('groups.set_user_as_owner', array('contextParentActionKey'=>$contextParentAction->getKey(),
                'userId'=>$userId, 'groupOwnerId'=>$this->groupDto->userId, 'groupId'=>$this->groupDto->id, 'contextActionMenu'=>$contextActionMenu, 'managerIds' => $this->managerIds));
            OW::getEventManager()->trigger($event);

        }

        return $contextActionMenu;
    }

    public function getFields( $userIdList )
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate !== null && $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex !== null && $qSex->onView )
            $qs[] = 'sex';

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach ( $questionList as $uid => $question )
        {

            $fields[$uid] = array();

            $age = '';

            if ( !empty($question['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            $sexValue = '';
            if ( !empty($question['sex']) )
            {
                $sex = $question['sex'];

                for ( $i = 0; $i < 64; $i++ )
                {
                    $val = $i+1;
                    if ( (int) $sex == $val )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $fields[$uid][] = array(
                    'label' => '',
                    'value' => $sexValue . ' ' . $age
                );
            }
        }

        return $fields;
    }
}


class GROUPS_GroupForm extends Form
{
    public function __construct( $formName )
    {
        parent::__construct($formName);

        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $language = OW::getLanguage();

        $field = new TextField('title');
        $field->setRequired(true);
        $field->setLabel($language->text('groups', 'create_field_title_label'));
        $this->addElement($field);
        $unsetKeys=array("Table");
        $field = new WysiwygTextarea('description','groups',null,true,$unsetKeys);
        $field->setLabel($language->text('groups', 'create_field_description_label'));
        $field->setRequired(true);
        $this->addElement($field);

        $field = new GROUPS_Image('image');
        $field->setLabel($language->text('groups', 'create_field_image_label'));
        $field->addValidator(new GROUPS_ImageValidator());
        $this->addElement($field);

        $whoCanView = new RadioField('whoCanView');
        $whoCanView->setRequired();
        $whoCanView->addOptions(
            array(
                GROUPS_BOL_Service::WCV_ANYONE => $language->text('groups', 'form_who_can_view_anybody'),
                GROUPS_BOL_Service::WCV_INVITE => $language->text('groups', 'form_who_can_view_invite')
            )
        );
        $whoCanView->setLabel($language->text('groups', 'form_who_can_view_label'));
        $whoCanView->setValue(GROUPS_BOL_Service::WCV_ANYONE);
        $this->addElement($whoCanView);

        $whoCanInvite = new RadioField('whoCanInvite');
        $whoCanInvite->setRequired();
        $whoCanInvite->addOptions(
            array(
                GROUPS_BOL_Service::WCI_PARTICIPANT => $language->text('groups', 'form_who_can_invite_participants'),
                GROUPS_BOL_Service::WCI_CREATOR => $language->text('groups', 'form_who_can_invite_creator')
            )
        );
        $whoCanInvite->setLabel($language->text('groups', 'form_who_can_invite_label'));
        $whoCanInvite->setValue(GROUPS_BOL_Service::WCI_PARTICIPANT);
        $this->addElement($whoCanInvite);
    }

    /**
     *
     * @param GROUPS_BOL_Group $group
     * @return GROUPS_BOL_Group
     */
    public function processGroup( GROUPS_BOL_Group $group )
    {
        $values = $this->getValues();
        $service = GROUPS_BOL_Service::getInstance();
        return $service->processGroupInfo($group, $values);
    }

    public function process()
    {

    }
}

class GROUPS_CreateGroupForm extends GROUPS_GroupForm
{

    public function __construct()
    {
        parent::__construct('GROUPS_CreateGroupForm');

        $this->getElement('title')->addValidator(new GROUPS_UniqueValidator());

        $field = new Submit('save');
        $field->setValue(OW::getLanguage()->text('groups', 'create_submit_btn_label'));
        $this->addElement($field);
    }

    /**
     * (non-PHPdoc)
     * @see ow_plugins/groups/controllers/GROUPS_GroupForm#process()
     */
    public function process()
    {

    }
}

class GROUPS_EditGroupForm extends GROUPS_GroupForm
{
    /**
     *
     * @var GROUPS_BOL_Group
     */
    private $groupDto;

    public function __construct( GROUPS_BOL_Group $group )
    {
        parent::__construct('GROUPS_EditGroupForm');

        $this->groupDto = $group;

        $this->getElement('title')->setValue($group->title);
        $this->getElement('title')->addValidator(new GROUPS_UniqueValidator($group->title));
        $this->getElement('description')->setValue($group->description);
        $this->getElement('whoCanView')->setValue($group->whoCanView);
        $this->getElement('whoCanInvite')->setValue($group->whoCanInvite);

        $deleteImageField = new HiddenField('deleteGroupImage');
        $deleteImageField->setId('deleteGroupImage');
        $deleteImageField->setValue('false');
        $this->addElement($deleteImageField);

        $field = new Submit('save');
        $field->setValue(OW::getLanguage()->text('groups', 'edit_submit_btn_label'));
        $field->addAttribute('onclick', "$(this).addClass('ow_inprogress');");
        $this->addElement($field);
    }

    /**
     * (non-PHPdoc)
     * @see ow_plugins/groups/controllers/GROUPS_GroupForm#process()
     */
    public function process()
    {
        $result = $this->processGroup($this->groupDto);

        if ( $result )
        {
            $event = new OW_Event(GROUPS_BOL_Service::EVENT_EDIT, array('groupId' => $this->groupDto->id));
            OW::getEventManager()->trigger($event);
        }

        return $result;
    }
}

class GROUPS_ImageValidator extends OW_Validator
{

    public function __construct()
    {

    }

    /**
     * @see OW_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        if ( empty($value) )
        {
            return true;
        }

        $realName = $value['name'];
        $tmpName = $value['tmp_name'];

        switch ( false )
        {
            case is_uploaded_file($tmpName):
                $this->setErrorMessage(OW::getLanguage()->text('groups', 'errors_image_upload'));
                return false;

            case UTIL_File::validateImage($realName):
                $this->setErrorMessage(OW::getLanguage()->text('groups', 'errors_image_invalid'));
                return false;
        }

        return true;
    }
}

class GROUPS_Image extends FileField
{

    public function getValue()
    {
        return empty($_FILES[$this->getName()]['tmp_name']) ? null : $_FILES[$this->getName()];
    }
}

class GROUPS_UniqueValidator extends OW_Validator
{
    private $exception;

    public function __construct( $exception = null )
    {
        $this->setErrorMessage(OW::getLanguage()->text('groups', 'group_already_exists'));

        $this->exception = $exception;
    }

    public function isValid( $value )
    {
        if ( !empty($this->exception) && trim($this->exception) == trim($value) )
        {
            return true;
        }

        $dto = GROUPS_BOL_Service::getInstance()->findByTitle($value);

        if ( $dto === null )
        {
            return true;
        }

        return false;
    }
}
