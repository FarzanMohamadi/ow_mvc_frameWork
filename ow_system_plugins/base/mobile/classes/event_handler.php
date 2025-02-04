<?php
/**
 * @package ow_core
 * @since 1.0
 */
class BASE_MCLASS_EventHandler extends BASE_CLASS_EventHandler
{

    public function init()
    {
        $this->genericInit();
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'onDocRenderAddJsDeclarations'));
        $eventManager->bind(BASE_MCMP_ProfileContentMenu::EVENT_NAME, array($this, 'onMobileProfileContentMenu'));
        //$eventManager->bind(BASE_MCMP_ProfileContentMenu::EVENT_NAME, array($this, 'onFakeMobileProfileContentMenu'));

        $eventManager->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddDeleteActionTool'));
        $eventManager->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddSuspendActionTool'));
        $eventManager->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddUserApproveActionTool'));
        $eventManager->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddUserFeatureActionTool'));
        $eventManager->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddUserBlockActionTool'));
        $eventManager->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddUserBlockedActionTool'));

        $eventManager->bind('base.members_only_exceptions', array($this, 'onAddMembersOnlyException'));
        $eventManager->bind('base.password_protected_exceptions', array($this, 'onAddPasswordProtectedExceptions'));
        $eventManager->bind('base.maintenance_mode_exceptions', array($this, 'onAddMaintenanceModeExceptions'));

        $eventManager->bind(OW_EventManager::ON_PLUGINS_INIT, array($this, 'onPluginsInitCheckUserStatus'));
        $eventManager->bind('mobile.notifications.on_item_render', array($this, 'onNotificationRender'));
        $eventManager->bind(BASE_MCMP_ConnectButtonList::HOOK_REMOTE_AUTH_BUTTON_LIST, array($this, "onCollectButtonList"));
        $eventManager->bind('class.get_instance', array($this, "onGetClassInstance"));
        
        $eventManager->bind("base.user_list.get_fields", array($this, 'getUserListFields'));
        $eventManager->bind('join.get_captcha_field', array($this, 'getCaptcha'));
    }
    public function getCaptcha( OW_Event $e )
    {
        $e->setData(new CaptchaField('captchaField'));
    }
    public function onGetClassInstance( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !empty($params['className']) && $params['className'] == 'BASE_CLASS_AvatarFieldValidator' )
        {
            $rClass = new ReflectionClass('BASE_MCLASS_JoinAvatarFieldValidator');
            
            $arguments = array();
            
            if ( !empty($params['arguments']) )
            {
                $arguments = $params['arguments'];
            }
            
            $event->setData($rClass->newInstanceArgs($arguments));
        }
    }
    
    public function onCollectButtonList( BASE_CLASS_EventCollector $e )
    {
        if(!OW::getConfig()->getValue('base', 'disable_signup_button'))
        {
            $button = new BASE_MCMP_JoinButton();
            $e->add(array('iconClass' => 'ow_ico_signin_f', 'markup' => $button->render()));
        }
    }

    public function onBeforeDecoratorRender( BASE_CLASS_PropertyEvent $e )
    {
        switch ( $e->getProperty('decoratorName') )
        {
            case 'avatar_item':

                //todo : to make config in frmwidgetplus settings controlling label characters ( This part is commented since avatar labels are to be printed completely )
/*                if ( $e->getProperty('fullLabel') === null )
                {
                    $labelCharacterCount=1;
                    if (OW::getConfig()->configExists('frmwidgetplus', 'label_character_count')){
                        $labelCharacterCount=OW::getConfig()->getValue('frmwidgetplus', 'label_character_count');
                    }
                    $e->setProperty('label', mb_substr($e->getProperty('label'), 0, $labelCharacterCount));
                }*/

                break;
        }
    }

    public function onAddMaintenanceModeExceptions( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'standardSignIn'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'forgotPassword'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPasswordRequest'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPassword'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPasswordCodeExpired'));
    }

    public function onAddPasswordProtectedExceptions( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'standardSignIn'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'forgotPassword'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPasswordRequest'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPassword'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPasswordCodeExpired'));
        $event->add(array('controller' => 'BASE_MCTRL_BaseDocument', 'action' => 'redirectToDesktop'));
    }

    public function onAddMembersOnlyException( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'standardSignIn'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'signIn'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'forgotPassword'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPasswordRequest'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPassword'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPasswordCodeExpired'));
        $event->add(array('controller' => 'BASE_MCTRL_BaseDocument', 'action' => 'redirectToDesktop'));
        $event->add(array('controller' => 'BASE_MCTRL_Join', 'action' => 'index'));
        $event->add(array('controller' => 'BASE_MCTRL_Join', 'action' => 'joinFormSubmit'));
        $event->add(array('controller' => 'BASE_MCTRL_Join', 'action' => 'ajaxResponder'));
    }

    public function onMobileProfileContentMenu( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $lang = OW::getLanguage();
        $userName = BOL_UserService::getInstance()->getUserName($userId);
        $url = OW::getRouter()->urlForRoute('base_about_profile', array('username' => $userName));
        $resultArray = array(
            BASE_MCMP_ProfileContentMenu::DATA_KEY_LABEL => $lang->text('mobile', 'about'),
            BASE_MCMP_ProfileContentMenu::DATA_KEY_LINK_HREF => $url,
            BASE_MCMP_ProfileContentMenu::DATA_KEY_LINK_CLASS => 'owm_profile_nav_about'
        );

        $event->add($resultArray);
    }

    public function onDocRenderAddJsDeclarations( $e )
    {
        // Langs
        OW::getLanguage()->addKeyForJs('base', 'flag_as');
        OW::getLanguage()->addKeyForJs('base', 'authorization_limited_permissions');
        
        $scriptGen = UTIL_JsGenerator::newInstance()->setVariable(
                array('OWM', 'ajaxComponentLoaderRsp'), OW::getRouter()->urlFor('BASE_MCTRL_AjaxLoader', 'component')
        );
        $scriptGen->setVariable(array('OWM', 'ajaxAttachmentLinkRsp'), OW::getRouter()->urlFor('BASE_CTRL_Attachment', 'addLink'));

        //UsersApi
        $scriptGen->newObject(array('OW', 'Users'), 'OWM_UsersApi', array(array(
                "rsp" => OW::getRouter()->urlFor('BASE_CTRL_AjaxUsersApi', 'rsp')
            )));

        // Right console initialization
        if ( OW::getUser()->isAuthenticated() )
        {
            OW::getLanguage()->addKeyForJs('base', 'mobile_disabled_item_message');
            $params = array(
                'pages' => MBOL_ConsoleService::getInstance()->getPages(),
                'rspUrl' => OW::getRouter()->urlFor('BASE_MCTRL_Ping', 'index'),
                'lastFetchTime' => time(),
                'pingInterval' => FRMSecurityProvider::getDefaultPingIntervalInSeconds(),
                'desktopUrl' => OW::getRouter()->urlForRoute('base.desktop_version')
            );

            $scriptGen->addScript('
            var mconsole = new OWM_Console(' . json_encode($params) . ');
            mconsole.init();
        ');
        }

        OW::getDocument()->addScriptDeclaration($scriptGen->generateJs());
    }

    public function onUserToolbar( BASE_CLASS_EventCollector $e )
    {
        //TODO

        $e->add(array(
            "label" => "Block",
            "order" => 4,
            "group" => "addition",
            "class" => "owm_red_btn"
        ));

        $e->add(array(
            "label" => "Send Message",
            "order" => 1
        ));

        $e->add(array(
            "label" => "Follow",
            "order" => 2
        ));

        $e->add(array(
            "label" => "Mark as Featured",
            "order" => 3,
            "group" => "addition"
        ));



        $e->add(array(
            "label" => "Delete",
            "order" => 5,
            "group" => "addition",
            "class" => "owm_red_btn"
        ));

        $e->add(array(
            "label" => "Suspend",
            "order" => 6,
            "group" => "addition",
            "class" => "owm_red_btn"
        ));
    }

    public function onActionToolbarAddUserBlockActionTool( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        if ( empty($params['userId']) )
        {
            return;
        }

        if ( $params['userId'] == OW::getUser()->getId() )
        {
            return;
        }

        $authorizationService = BOL_AuthorizationService::getInstance();

        if ( $authorizationService->isActionAuthorizedForUser($params['userId'], 'base') || $authorizationService->isSuperModerator($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $resultArray = array();

        $uniqId = FRMSecurityProvider::generateUniqueId("block-");
        $isBlocked = BOL_UserService::getInstance()->isBlocked($userId, OW::getUser()->getId());

        $resultArray["label"] = $isBlocked ? OW::getLanguage()->text('base', 'user_unblock_btn_lbl') : OW::getLanguage()->text('base', 'user_block_btn_lbl');

        $toggleText = !$isBlocked ? OW::getLanguage()->text('base', 'user_unblock_btn_lbl') : OW::getLanguage()->text('base', 'user_block_btn_lbl');

        $toggleClass = !$isBlocked ? 'owm_context_action_list_item' : 'owm_context_action_list_item owm_red_btn';

        $resultArray["attributes"] = array();
        $resultArray["attributes"]["data-command"] = $isBlocked ? "unblock" : "block";

        $toggleCommand = !$isBlocked ? "unblock" : "block";

        $resultArray["href"] = 'javascript://';
        $resultArray["id"] = $uniqId;
        $unBlockCode = '';
        $blockCode = '';
        $eData = array(
            "userId" => $userId,
            "toggleText" => $toggleText,
            "toggleCommand" => $toggleCommand,
            "toggleClass" => $toggleClass,
            "msg" => strip_tags(OW::getLanguage()->text("base", "user_block_confirm_message")),
            "unBlockCode" => $unBlockCode,
            "blockCode" => $blockCode
        );
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'userBlock_core')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $eData['blockCode'] = $frmSecuritymanagerEvent->getData()['code'];
        }
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'userUnBlock_core')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $eData['unBlockCode'] = $frmSecuritymanagerEvent->getData()['code'];
        }

        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent("#" . $uniqId, "click",
            'var toggle = false; if ( $(this).attr("data-command") == "block" && confirm(e.data.msg) ) { OWM.Users.blockUser(e.data.userId,e.data.blockCode); toggle = true; };
            if ( $(this).attr("data-command") != "block") { OWM.Users.unBlockUser(e.data.userId,e.data.unBlockCode); toggle =true;}
            toggle && OWM.Utils.toggleText($("span:eq(0)", this), e.data.toggleText);
            toggle && OWM.Utils.toggleAttr(this, "class", e.data.toggleClass);
            toggle && OWM.Utils.toggleAttr(this, "data-command", e.data.toggleCommand);',
            array("e"), $eData);

        OW::getDocument()->addOnloadScript($js);

        $resultArray["key"] = "base.block_user";
        $resultArray["group"] = "addition";

        $resultArray["class"] = $isBlocked ? '' : 'owm_red_btn';
        $resultArray["order"] = 3;

        $event->add($resultArray);
    }

    public function onActionToolbarAddUserFeatureActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $action = array(
            "group" => 'addition',
            "label" => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            "order" => 2
        );

        $userId = (int) $params['userId'];

        $uniqId = FRMSecurityProvider::generateUniqueId("feature-");
        $isFeatured = BOL_UserService::getInstance()->isUserFeatured($userId);

        $action["label"] = $isFeatured ? OW::getLanguage()->text('base', 'user_action_unmark_as_featured') : OW::getLanguage()->text('base', 'user_action_mark_as_featured');

        $toggleText = !$isFeatured ? OW::getLanguage()->text('base', 'user_action_unmark_as_featured') : OW::getLanguage()->text('base', 'user_action_mark_as_featured');

        $action["attributes"] = array();
        $action["attributes"]["data-command"] = $isFeatured ? "unfeature" : "feature";

        $toggleCommand = !$isFeatured ? "unfeature" : "feature";

        $action["href"] = 'javascript://';
        $action["id"] = $uniqId;
        $featureCode = '';
        $UnFeatureCode='';
        $eData = array(
            "userId" => $userId,
            "toggleText" => $toggleText,
            "toggleCommand" => $toggleCommand,
            "featureCode" => $featureCode,
            "unFeatureCode" => $UnFeatureCode
        );
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'userFeature_core')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $eData['featureCode'] = $frmSecuritymanagerEvent->getData()['code'];
        }
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'userUnFeature_core')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $eData['unFeatureCode'] = $frmSecuritymanagerEvent->getData()['code'];
        }
        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent("#" . $uniqId, "click",
            'OWM.Users[$(this).attr("data-command") == "feature" ? "featureUser" : "unFeatureUser"](e.data.userId,e.data.featureCode,e.data.unFeatureCode);
            OWM.Utils.toggleText($("span:eq(0)", this), e.data.toggleText);
            OWM.Utils.toggleAttr(this, "data-command", e.data.toggleCommand);'
            , array("e"), $eData);

        OW::getDocument()->addOnloadScript($js);

        $action["key"] = "base.make_featured";
        $event->add($action);
    }

    public function onActionToolbarAddUserApproveActionTool( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];
        $hasAccessToApproveUser = BOL_UserService::getInstance()->hasAccessToApproveUser($userId);
        if (!$hasAccessToApproveUser['valid']) {
            return;
        }

        if ( BOL_UserService::getInstance()->isApproved($userId) )
        {
            return;
        }

        $action = array(
            "group" => 'addition',
            "href" => OW::getRouter()->urlFor('BASE_CTRL_User', 'approve', array('userId' => $userId)),
            "label" => OW::getLanguage()->text('base', 'profile_toolbar_user_approve_label'),
            "class" => '',
            "key" => "base.approve_user",
            "order" => 1
        );

        $event->add($action);

        // request change
        $action = array(
            "group" => 'addition',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES => ['onclick'=>'request_change()'],
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_user_request_change_label'),
            "key" => "base.request_change_user"
        );
        $event->add($action);
        $js = "function request_change(){
            floatBox = OW.ajaxFloatBox('BASE_CMP_CommentRequestChangeMessage', [{userId:$userId}]); 
        }";
        OW::getDocument()->addScriptDeclarationBeforeIncludes($js);
    }

    public function onActionToolbarAddSuspendActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        if ( BOL_AuthorizationService::getInstance()->isSuperModerator($params['userId']) )
        {
            return;
        }

        $userService = BOL_UserService::getInstance();
        $userId = (int) $params['userId'];

        $action = array(
            "group" => 'addition',
            "label" => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            "order" => 5
        );

        $action["href"] = 'javascript://';

        $uniqId = FRMSecurityProvider::generateUniqueId('pat-suspend-');
        $action["id"] = $uniqId;

        $toogleText = null;
        $toggleCommand = null;
        $toggleClass = null;

        $suspended = $userService->isSuspended($userId);

        $action["attributes"] = array();
        $action["label"] = $suspended ? OW::getLanguage()->text('base', 'user_unsuspend_btn_lbl') : OW::getLanguage()->text('base', 'user_suspend_btn_lbl');

        $toggleText = !$suspended ? OW::getLanguage()->text('base', 'user_unsuspend_btn_lbl') : OW::getLanguage()->text('base', 'user_suspend_btn_lbl');

        $action["attributes"]["data-command"] = $suspended ? "unsuspend" : "suspend";

        $toggleCommand = !$suspended ? "unsuspend" : "suspend";

        $action["class"] = $suspended ? "" : "owm_red_btn";

        $toggleClass = !$suspended ? "owm_context_action_list_item" : "owm_context_action_list_item owm_red_btn";
        $suspendCode='';
        $unSuspendCode='';
        $eData = array(
            "userId" => $userId,
            "toggleText" => $toggleText,
            "toggleCommand" => $toggleCommand,
            "toggleClass" => $toggleClass,
            "suspendCode" => $suspendCode,
            "unSuspendCode" => $unSuspendCode
        );
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'userSuspend_core')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $eData['suspendCode'] = $frmSecuritymanagerEvent->getData()['code'];
        }
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'userUnSuspend_core')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $eData['unSuspendCode'] = $frmSecuritymanagerEvent->getData()['code'];
        }
        $rsp = OW::getRouter()->urlFor('BASE_CTRL_SuspendedUser', 'ajaxRsp');
        $rsp = OW::getRequest()->buildUrlQueryString($rsp, array(
                "userId" => $userId
            ));

        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent("#" . $uniqId, "click",
            'OWM.Users[$(this).attr("data-command") == "suspend" ? "suspendUser" : "unSuspendUser"](e.data.userId, e.data.suspendCode,e.data.unSuspendCode);
            OWM.Utils.toggleText($("span:eq(0)", this), e.data.toggleText);
            OWM.Utils.toggleAttr(this, "class", e.data.toggleClass);
            OWM.Utils.toggleAttr(this, "data-command", e.data.toggleCommand);'
            , array("e"), $eData);

        OW::getDocument()->addOnloadScript($js);

        $action["key"] = "base.suspend_user";

        $event->add($action);
    }

    public function onActionToolbarAddDeleteActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        if ( BOL_AuthorizationService::getInstance()->isSuperModerator($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $confirmMsg = OW::getLanguage()->text('base', 'are_you_sure');
        $callbackUrl = OW::getRouter()->urlFor('BASE_MCTRL_User', 'userDeleted');
        $code='';
        $eData = array('userId' => $userId, "confirmMsg" => $confirmMsg, 'callbackUrl' => $callbackUrl, 'code'=>$code);
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$userId,'isPermanent'=>true,'activityType'=>'userDelete_core')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $eData['code'] = $frmSecuritymanagerEvent->getData()['code'];
        }
        $linkId = 'ud' . rand(10, 1000000);

        $resultArray = array(
            "label" => OW::getLanguage()->text('base', 'profile_toolbar_user_delete_label'),
            "class" => 'owm_red_btn',
            "href" => 'javascript://',
            "id" => $linkId,
            "group" => 'addition',
            "order" => 5,
            "key" => "base.delete_user"
        );

        $newDeletePath = OW::getEventManager()->trigger(new OW_Event('base.before.action_user_delete', array('href' => 'javascript://', 'userId' => $userId)));
        if(isset($newDeletePath->getData()['href'])){
            $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF] = $newDeletePath->getData()['href'];
        }else{
            $script = UTIL_JsGenerator::newInstance()->jQueryEvent('#' . $linkId, 'click',
                'if (confirm(e.data.confirmMsg)) OWM.Users.deleteUser(e.data.userId,e.data.code, e.data.callbackUrl);'
                , array('e'),$eData);
            OW::getDocument()->addOnloadScript($script);
        }

        $event->add($resultArray);
    }

    public function onActionToolbarAddUserBlockedActionTool ( BASE_CLASS_EventCollector $event ) {
        $params = $event->getParams();

        if ( empty($params['userId']) ) {
            return;
        }

        if ($params['userId'] != OW::getUser()->getId()) {
            return;
        }

        $action = array(
            "group" => 'addition',
            "label" => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            "order" => 2
        );

        $uniqId = FRMSecurityProvider::generateUniqueId('users-blocked-');
        $action["label"] = OW::getLanguage()->text('base', 'my_blocked_users');
        $action["href"] = OW::getRouter()->urlForRoute('users-blocked');
        $action["id"] = $uniqId;
        $action["key"] = "base.users_blocked";

        $event->add($action);
    }

    public function onPluginsInitCheckUserStatus()
    {
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CHECK_USER_STATUS));
        if ( OW::getUser()->isAuthenticated() )
        {
            $user = BOL_UserService::getInstance()->findUserById(OW::getUser()->getId());

            $signOutDispatchAttrs = OW::getRouter()->getRoute('base_sign_out')->getDispatchAttrs();

            if ( empty($signOutDispatchAttrs['controller']) || empty($signOutDispatchAttrs['action']) )
            {
                $signOutDispatchAttrs['controller'] = 'BASE_CTRL_User';
                $signOutDispatchAttrs['action'] = 'signOut';
            }

            if ( OW::getConfig()->getValue('base', 'mandatory_user_approve') && !BOL_UserService::getInstance()->isApproved() && !OW::getUser()->isAdmin())
            {
                $redirectController = (FRMSecurityProvider::checkPluginActive('frmprofilemanagement', true))? 'FRMPROFILEMANAGEMENT_MCTRL_Edit':'BASE_MCTRL_WaitForApproval';
                OW::getRequestHandler()->setCatchAllRequestsAttributes('base.wait_for_approval', array('controller' => $redirectController, 'action' => 'index'));
                OW::getRequestHandler()->addCatchAllRequestsExclude('base.wait_for_approval', $signOutDispatchAttrs['controller'], $signOutDispatchAttrs['action']);
                OW::getRequestHandler()->addCatchAllRequestsExclude('base.wait_for_approval', 'BASE_MCTRL_AjaxLoader', 'component');
                OW::getRequestHandler()->addCatchAllRequestsExclude('base.wait_for_approval', 'BASE_MCTRL_Invitations', 'command');
                OW::getRequestHandler()->addCatchAllRequestsExclude('base.wait_for_approval', 'BASE_MCTRL_Ping', 'index');
            }

            if ( $user !== null )
            {
                if ( BOL_UserService::getInstance()->isSuspended($user->getId()) && !OW::getUser()->isAdmin() )
                {
                    OW::getRequestHandler()->setCatchAllRequestsAttributes('base.suspended_user', array('controller' => 'BASE_MCTRL_SuspendedUser', 'action' => 'index'));
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', $signOutDispatchAttrs['controller'], $signOutDispatchAttrs['action']);
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'BASE_MCTRL_AjaxLoader');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'BASE_MCTRL_Invitations', 'command');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'BASE_MCTRL_Ping', 'index');
                }

                $useVerifyEmailRedirect = true;
                $verifyEmailEvent = OW::getEventManager()->trigger(new OW_Event('base.on_before_email_verify_page_redirected'));
                if(isset($verifyEmailEvent->getData()['do-not-show'])){
                    $useVerifyEmailRedirect = false;
                }
                if ( $useVerifyEmailRedirect && (int) $user->emailVerify === 0 && OW::getConfig()->getValue('base', 'confirm_email') )
                {
                    OW::getRequestHandler()->setCatchAllRequestsAttributes('base.email_verify', array(OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_MCTRL_EmailVerify', OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'index'));

                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.email_verify', $signOutDispatchAttrs['controller'], $signOutDispatchAttrs['action']);
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.email_verify', 'BASE_MCTRL_EmailVerify');
                }

                $accountType = BOL_QuestionService::getInstance()->findAccountTypeByName($user->accountType);

                if ( empty($accountType) )
                {
                    OW::getRequestHandler()->setCatchAllRequestsAttributes('base.complete_profile.account_type', array('controller' => 'BASE_MCTRL_CompleteProfile', 'action' => 'fillAccountType'));
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile.account_type', $signOutDispatchAttrs['controller'], $signOutDispatchAttrs['action']);
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile.account_type', 'BASE_MCTRL_AjaxLoader');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile.account_type', 'BASE_MCTRL_Invitations');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile.account_type', 'BASE_MCTRL_Ping');
                }
                else
                {
                    $questionsEditStamp = OW::getConfig()->getValue('base', 'profile_question_edit_stamp');
                    $updateDetailsStamp = BOL_PreferenceService::getInstance()->getPreferenceValue('profile_details_update_stamp', OW::getUser()->getId());

                    if ( $questionsEditStamp >= (int) $updateDetailsStamp )
                    {
                        require_once OW_DIR_CORE . 'validator.php';
                        $questionList = BOL_QuestionService::getInstance()->getEmptyRequiredQuestionsList($user->id);

                        if ( !empty($questionList) )
                        {
                            OW::getEventManager()->trigger(new OW_Event('frmmobilesupport.exclude.catch.request'));
                            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.complete_profile', array('controller' => 'BASE_MCTRL_CompleteProfile', 'action' => 'fillRequiredQuestions'));
                            OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile', $signOutDispatchAttrs['controller'], $signOutDispatchAttrs['action']);
                            OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile', 'BASE_MCTRL_AjaxLoader');
                            OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile', 'BASE_MCTRL_Invitations');
                            OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile', 'BASE_MCTRL_Ping');
                        }
                        else
                        {
                            BOL_PreferenceService::getInstance()->savePreferenceValue('profile_details_update_stamp', time(), OW::getUser()->getId());
                            if (OW::getConfig()->getValue('base', 'mandatory_user_approve') && !BOL_AuthorizationService::getInstance()->isSuperModerator(OW::getUser()->getId()) && !BOL_UserService::getInstance()->isApproved()) {
                                OW::getFeedback()->info(OW::getLanguage()->text('base', 'wait_for_approval'));
                                OW_User::getInstance()->logout();
                                OW::getApplication()->redirect(OW_URL_HOME);
                            }
                        }
                    }
                }
            }
            else
            {
                OW::getUser()->logout();
            }
        }
    }

    public function onNotificationRender( OW_Event $event )
    {
        $params = $event->getParams();
        if ( in_array($params['entityType'],
            ['base_profile_wall', 'user-edit-approve' , 'user-add-approve' ] ))
        {
            $data = $params['data'];
            $event->setData($data);
        }
    }
    
    public function getUserListFields( OW_Event $e )
    {
        $params = $e->getParams();
        
        $list = !empty($params['list']) ? $params['list'] : null;
        $userIdList = !empty($params['userIdList']) ? $params['userIdList'] : null;
        
        if ( empty($userIdList) )
        {
            return;
        }
        
        $fieldsList = array();
        $qBirthdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');
        
        if ( $qBirthdate->onView )
        {
            $fieldsList[] = 'birthdate';
        }

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
        {
            $fieldsList[] = 'sex';
        }
        
        $questionList = BOL_QuestionService::getInstance()->findQuestionByNameList($fieldsList);
        $qData = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $fieldsList);
        
        $data = $e->getData();
        
        foreach ( $userIdList as $userId )
        {
            if(!isset($qData[$userId])){
                continue;
            }
            $questionsData = $qData[$userId];
            
            $age = '';
            if ( !empty($questionsData['birthdate']))
            {
                $date = UTIL_DateTime::parseDate($questionsData['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            $sexValue = '';
            if ( !empty($questionsData['sex']) )
            {
                $sexValue = BOL_QuestionService::getInstance()->getQuestionValueForUserList($questionList['sex'], $questionsData['sex']);
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $data[$userId][] = $sexValue . ' ' . $age;
            }
            
            switch( $list )
            {
                case "birthdays":
                    if ( !empty($questionsData['birthdate']) && $list == 'birthdays'  )
                    {
                        $dinfo = date_parse($questionsData['birthdate']);
                        $birthdate = '';

                        if ( intval(date('d')) + 1 == intval($dinfo['day']) )
                        {
                            $birthday= OW::getLanguage()->text('base', 'date_time_tomorrow');
                            $birthdate = '<a href="#" class="ow_lbutton ow_green">' . $birthday . '</a>';
                        }
                        else if ( intval(date('d')) == intval($dinfo['day']) )
                        {
                            $birthday = OW::getLanguage()->text('base', 'date_time_today');
                            $birthdate = '<a href="#" class="ow_lbutton ow_green">' . $birthday . '</a>';
                        }
                        else
                        {
                            $birthdate = UTIL_DateTime::formatBirthdate($dinfo['year'], $dinfo['month'], $dinfo['day']);
                        }

                        $data[$userId][] = OW::getLanguage()->text('birthdays', 'birthday') . ":" . $birthdate;
                    }
                break;
            }
        }
        
        $e->setData($data);
    }
}
