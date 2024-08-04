<?php
/**
 * Permission controller.
 *
 * @package ow_core
 * @since 1.0
 */
class ADMIN_CTRL_Permissions extends ADMIN_CTRL_Abstract
{

    /**
     * @var BASE_CMP_ContentMenu
     */
    //private $contentMenu;

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(OW::getLanguage()->text('admin', 'permissions_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_lock');
    }

    public function index()
    {
        $language = OW::getLanguage();
        $config = OW::getConfig();
        $baseConfigs = $config->getValues('base');

        $form = new Form('privacy_settings');

        $userApprove = new CheckboxField('user_approve');
        $userApprove->setLabel($language->text('admin', 'permissions_index_user_approve'));
        $form->addElement($userApprove);

        $whoCanJoin = new RadioField('who_can_join');
        $whoCanJoin->addOptions(array('1' => $language->text('admin', 'permissions_index_anyone_can_join'), '2' => $language->text('admin', 'permissions_index_by_invitation_only_can_join')));
        $whoCanJoin->setLabel($language->text('admin', 'permissions_index_who_can_join'));
        $form->addElement($whoCanJoin);

        $whoCanInvite = new RadioField('who_can_invite');
        $whoCanInvite->addOptions(array('1' => $language->text('admin', 'permissions_index_all_users_can_invate'), '2' => $language->text('admin', 'permissions_index_admin_only_can_invate')));
        $whoCanInvite->setLabel($language->text('admin', 'permissions_index_who_can_invite'));
        $form->addElement($whoCanInvite);

        $guestsCanView = new RadioField('guests_can_view');
        $guestsCanView->addOptions(array('1' => $language->text('admin', 'permissions_index_yes'), '2' => $language->text('admin', 'permissions_index_no'), '3' => $language->text('admin', 'permissions_index_with_password')));
        $guestsCanView->setLabel($language->text('admin', 'permissions_index_guests_can_view_site'));
        $guestsCanView->setDescription($language->text('admin', 'permissions_idex_if_not_yes_will_override_settings'));
        $form->addElement($guestsCanView);

        $password = new TextField('password');
        $password->setHasInvitation(true);
        if($baseConfigs['guests_can_view'] == 3)
        {
            $password->setInvitation($language->text('admin', 'change_password'));
        }
        else
        {
            $password->setInvitation($language->text('admin', 'add_password'));
        }
        $form->addElement($password);

        $submit = new Submit('save');
        $submit->setValue($language->text('admin', 'permissions_index_save'));
        $form->addElement($submit);

        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                $config->saveConfig('base', 'who_can_join', (int) $data['who_can_join']);
                $config->saveConfig('base', 'who_can_invite', (int) $data['who_can_invite']);
                $config->saveConfig('base', 'mandatory_user_approve', ((bool) $data['user_approve'] ? 1 : 0));

                if ( (int) $data['guests_can_view'] === 3 && empty($data['password']) )
                {
                    OW::getFeedback()->error($language->text('admin', 'permission_global_privacy_empty_pass_error_message'));
                    return;
                }
                else if ( (int) $data['guests_can_view'] === 3 && strlen(trim($data['password'])) < 4 )
                {
                    OW::getFeedback()->error($language->text('admin', 'permission_global_privacy_pass_length_error_message'));
                    return;
                }
                else
                {
                    $adminEmail = OW::getUser()->getEmail();
                    $senderMail = $config->getValue('base', 'site_email');
                    $mail = OW::getMailer()->createMail();
                    $mail->addRecipientEmail($adminEmail);
                    $mail->setSender($senderMail);
                    $mail->setSenderSuffix(false);
                    $mail->setSubject(OW::getLanguage()->text( 'admin', 'site_password'));
                    $mail->setTextContent( OW::getLanguage()->text( 'admin', 'admin_password', array('password' => $data['password'])));
                    try
                    {
                        OW::getMailer()->send($mail);
                    }
                    catch (Exception $e)
                    {
                        OW::getLogger()->writeLog( OW_Log::ERROR,'admin.send_password_message', array('message'=>$e->getMessage()));
                    }

                    $data['password'] = FRMSecurityProvider::getInstance()->hashSha256Data($data['password']);
                    $config->saveConfig('base', 'guests_can_view', (int) $data['guests_can_view']);
                    $config->saveConfig('base', 'guests_can_view_password', $data['password']);
                }

                OW::getFeedback()->info($language->text('admin', 'permission_global_privacy_settings_success_message'));
                $this->redirect();
            }
        }

        $baseConfigs = $config->getValues('base');
        $form->getElement('who_can_join')->setValue($baseConfigs['who_can_join']);
        $form->getElement('who_can_invite')->setValue($baseConfigs['who_can_invite']);
        $form->getElement('guests_can_view')->setValue($baseConfigs['guests_can_view']);
        $form->getElement('user_approve')->setValue($baseConfigs['mandatory_user_approve']);
    }

    public function roles()
    {
        $service = BOL_AuthorizationService::getInstance();
        $this->assign('formAction', OW::getRouter()->urlFor(__CLASS__, 'savePermissions'));

        $roles = $service->getRoleList();
        $actions = $service->getActionList();
        $groups = $service->getGroupList();
        $permissions = $service->getPermissionList();

        $groupActionList = array();

        foreach ( $groups as $group )
        {
            /* @var $group BOL_AuthorizationGroup */
            $groupActionList[$group->id]['name'] = $group->name;
            $groupActionList[$group->id]['actions'] = array();
        }

        foreach ( $actions as $action )
        {
            /* @var $action BOL_AuthorizationAction */
            $groupActionList[$action->groupId]['actions'][] = $action;
        }

        foreach ( $groupActionList as $key => $value )
        {
            if ( count($value['actions']) === 0 || !OW::getPluginManager()->isPluginActive($value['name']) )
            {
                unset($groupActionList[$key]);
            }
        }

        $perms = array();
        foreach ( $permissions as $permission )
        {
            /* @var $permission BOL_AuthorizationPermission */
            $perms[$permission->actionId][$permission->roleId] = true;
        }

        $tplRoles = array();
        foreach ( $roles as $role )
        {
            $tplRoles[$role->sortOrder] = $role;
        }

        ksort($tplRoles);

        $this->assign('perms', $perms);
        $this->assign('roles', $tplRoles);
        $this->assign('colspanForRoles', count($roles) + 1);
        $this->assign('groupActionList', $groupActionList);
        $this->assign('guestRoleId', $service->getGuestRoleId());

        // SD code below - collecting group labels
        $event = new BASE_CLASS_EventCollector('admin.add_auth_labels');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        $dataLabels = empty($data) ? array() : call_user_func_array('array_merge', $data);
        $this->assign('labels', $dataLabels);
    }

    public function moderators()
    {
        $service = BOL_AuthorizationService::getInstance();
        $this->assign('formAction', OW::getRouter()->urlFor(__CLASS__, 'process'));

        $moderators = $service->getModeratorList();
        $this->assign('moderators', $moderators);

        $users = array();
        $deleteModerUrls = array();
        $this->assign('numOfModerators',count($moderators));

        foreach ( $moderators as $moderator )
        {
            $users[] = $moderator->userId;
            $deleteModerUrls[$moderator->userId] = OW::getRouter()->urlFor(__CLASS__, 'deleteModerator', array('id' => $moderator->id));
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$moderator->userId,'isPermanent'=>true,'activityType'=>'delete_moderator')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $code = $frmSecuritymanagerEvent->getData()['code'];
                $deleteModerUrls[$moderator->userId] = OW::getRouter()->urlFor(__CLASS__, 'deleteModerator',
                    array('id' => $moderator->id,'code' =>$code));
            }
        }

        $this->assign('users', $users);
        $this->assign('deleteModerUrls', $deleteModerUrls);

        $this->assign('avatars', BOL_AvatarService::getInstance()->getDataForUserAvatars($users, true, true, true, false));

        $groups = $service->getGroupList(true);

        foreach ( $groups as $key => $group )
        {
            if ( !OW::getPluginManager()->isPluginActive($group->name) )
            {
                unset($groups[$key]);
            }
        }

        $this->assign('groups', $groups);

        $permissions = $service->getModeratorPermissionList();

        $perms = array();
        foreach ( $permissions as $permission )
        {
            $perms[$permission->moderatorId][$permission->groupId] = true;
        }

        $this->assign('perms', $perms);

        $this->assign('myModeratorId', $service->getModeratorIdByUserId(OW::getUser()->getId()));

        $this->assign('superModeratorId', $service->getModeratorIdByUserId($service->getSuperModeratorUserId()));

        $this->assign('adminGroupId', $service->getAdminGroupId());


        $this->assign('addFormAction', OW::getRouter()->urlFor(__CLASS__, 'addModerator'));

        // SD code below - collecting group labels
        $event = new BASE_CLASS_EventCollector('admin.add_auth_labels');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        $dataLabels = empty($data) ? array() : call_user_func_array('array_merge', $data);
        $groupLabels = array();

        /* @var $group BOL_AuthorizationGroup */
        foreach ( $groups as $group )
        {
            $groupLabels[$group->getName()] = empty($dataLabels[$group->getName()]['label']) ? $group->getName() : $dataLabels[$group->getName()]['label'];
        }

        $this->assign('groupLabels', $groupLabels);

        $this->setPageHeading(OW::getLanguage()->text('admin', 'sidebar_menu_item_permission_moders'));

        # --------------------- MENU
        if(FRMSecurityProvider::checkPluginActive('frmquestionroles', true)) {
            $menu = FRMQUESTIONROLES_CTRL_Admin::getMenu();
            $menu->setItemActive('users');
            $this->addComponent('menu', $menu);
        }
        # ---------------------
    }

    public function process()
    {
        if ( OW::getRequest()->isPost() && !empty($_POST['perm']) )
        {
            $perms = array();
            foreach ( $_POST['perm'] as $perm )
            {
                $moderatorGroupPair = explode(':', $perm);
                $permisson = new BOL_AuthorizationModeratorPermission();
                $permisson->moderatorId = (int) $moderatorGroupPair[0];
                $permisson->groupId = (int) $moderatorGroupPair[1];
                $perms[] = $permisson;
            }

            BOL_AuthorizationService::getInstance()->saveModeratorPermissionList($perms, OW::getUser()->getId());
        }

        OW::getFeedback()->info(OW::getLanguage()->text('admin', 'permissions_successfully_updated'));
        $this->redirect(OW::getRouter()->urlForRoute('admin_permissions_moderators'));
    }

    public function addModerator()
    {
        if ( OW::getRequest()->isPost() )
        {
            $username = trim($_POST['username']);
            $user = BOL_UserService::getInstance()->findByUsername($username);
            if ( $user === null )
            {
                OW::getFeedback()->error(OW::getLanguage()->text('admin', 'permissions_feedback_user_not_found'));
            }
            else
            {
                if ( BOL_AuthorizationService::getInstance()->addModerator($user->id) )
                {
                    OW::getFeedback()->info(OW::getLanguage()->text('admin', 'permissions_feedback_moderator_added'));
                }
                else
                {
                    OW::getFeedback()->warning(OW::getLanguage()->text('admin', 'permissions_feedback_user_is_already_moderator', array('username' => $username)));
                }
            }
        }

        $this->redirect(OW::getRouter()->urlForRoute('admin_permissions_moderators'));
    }

    public function deleteModerator( array $params )
    {
        //TODO REMOVE FROM MODERATORS
        if ( isset($params['id']) )
        {

            if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
                $code =$params['code'];
                if(!isset($code)){
                    throw new Redirect404Exception();
                }
                OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                    array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'delete_moderator')));
            }
            $removed = BOL_AuthorizationService::getInstance()->deleteModerator($params['id']);
            if ( $removed )
            {
                OW::getFeedback()->info(OW::getLanguage()->text('admin', 'permissions_feedback_user_kicked_from_moders'));
            }
            else
            {
                OW::getFeedback()->error(OW::getLanguage()->text('admin', 'permissions_feedback_cant_remove_moder'));
            }
        }
        else
        {
            OW::getFeedback()->error(OW::getLanguage()->text('admin', 'permissions_feedback_user_not_found'));
        }

        $this->redirect(OW::getRouter()->urlForRoute('admin_permissions_moderators'));
    }

    public function savePermissions()
    {
        if ( OW::getRequest()->isPost() )
        {
            $perms = array();
            foreach ( $_POST['perm'] as $perm )
            {
                $actionRolePair = explode(':', $perm);
                $permisson = new BOL_AuthorizationPermission();
                $permisson->actionId = (int) $actionRolePair[0];
                $permisson->roleId = (int) $actionRolePair[1];
                $perms[] = $permisson;
            }
            BOL_AuthorizationService::getInstance()->savePermissionList($perms);
        }
        OW::getFeedback()->info(OW::getLanguage()->text('admin', 'permissions_successfully_updated'));

        $this->redirect(OW::getRouter()->urlForRoute('admin_user_roles'));
    }
}