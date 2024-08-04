<?php
/**
 * Users action controller
 *
 * @package ow.ow_system_plugins.admin.controllers
 * @since 1.0
 */
class ADMIN_CTRL_Users extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns menu component
     *
     * @return BASE_CMP_ContentMenu
     */
    private function getMenu()
    {
        $language = OW::getLanguage();

        $menuItems = array();

        $keys = array('recent', 'suspended', 'unverified', 'unapproved');
        $labels = array('recently_active', 'suspended', 'unverified', 'unapproved');
        $icons = array('clock', 'delete', 'mail', 'flag');

        $approveEnabled = OW::getConfig()->getValue('base', 'mandatory_user_approve');
        foreach ( $keys as $ord => $key )
        {
            if ( $key == 'unapproved' && !$approveEnabled )
            {
                continue;
            }
            
            $urlParams = $key == 'recent' ? array() : array('list' => $key);

            $item = new BASE_MenuItem();
            $item->setLabel($language->text('admin', 'menu_item_users_' . $labels[$ord]));
            $item->setUrl(OW::getRouter()->urlForRoute('admin_users_browse', $urlParams));
            $item->setKey($key);
            $item->setIconClass('ow_dynamic_color_icon ow_ic_' . $icons[$ord]);
            $item->setOrder($ord);

            array_push($menuItems, $item);
        }
        $frmsmsEvent = OW::getEventManager()->trigger(new OW_Event('frmsms.on.get.users.list.menu.in.admin',array('menuItems' =>$menuItems,'order' => $ord)));
        if(isset($frmsmsEvent->getData()['menuItems'])){
            $menuItems = $frmsmsEvent->getData()['menuItems'];
        }
        return new BASE_CMP_ContentMenu($menuItems);
    }

    /**
     * User list page controller
     *
     * @param array $params
     */
    public function index( array $params )
    {
        $language = OW::getLanguage();
        
        OW::getDocument()->getMasterPage()->getMenu(OW_Navigation::ADMIN_USERS)->setItemActive('sidebar_menu_item_users');

        // invite members
        $form = new Form('invite-members');

        $hidden = new HiddenField('invite_members');
        $hidden->setValue('1');
        $form->addElement($hidden);

        $emails = new Textarea('emails');
        $form->addElement($emails);
        $emails->setRequired();
        $emails->setHasInvitation(true);
        $emails->setInvitation($language->text('admin', 'invite_members_textarea_invitation_text', array('limit' => (int)OW::getConfig()->getValue('base', 'user_invites_limit'))));

        $submit = new Submit('submit');
        $submit->setValue($language->text('admin', 'invite_members_submit_label'));
        $form->addElement($submit);

        $this->addForm($form);

        $addUserForm = new AddUserForm();
        $this->addForm($addUserForm);

        if ( OW::getRequest()->isPost() && isset($_POST['invite_members']) )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                $emails = array_unique(preg_split('/\n/', $data['emails']));

                $emailList = array();

                foreach ( $emails as $email )
                {
                    if ( UTIL_Validator::isEmailValid(trim($email)) )
                    {
                        $emailList[] = trim($email);
                    }
                }

                if ( sizeof($emailList) > (int)OW::getConfig()->getValue('base', 'user_invites_limit') )
                {
                    OW::getFeedback()->error($language->text('admin', 'invite_members_max_limit_message', array('limit' => (int)OW::getConfig()->getValue('base', 'user_invites_limit'))));
                    $form->getElement('emails')->setValue($data['emails']);
                    $this->redirect();
                }

                if ( empty($emailList) )
                {
                    OW::getFeedback()->error($language->text('admin', 'invite_members_min_limit_message'));
                    $form->getElement('emails')->setValue($data['emails']);
                    $this->redirect();
                }

                foreach ( $emailList as $email )
                {                    
                    BOL_UserService::getInstance()->sendAdminInvitation($email);
                    OW::getEventManager()->trigger(new OW_Event('frminvite.on.send.invitation', array('senderId'=>OW::getUser()->getId(),'invitedEmail'=>$email)));
                }

                OW::getFeedback()->info($language->text('admin', 'invite_members_success_message'));
                $this->redirect();
            }
        }

        $language->addKeyForJs('admin', 'invite_members_cap_label');
        $language->addKeyForJs('admin', 'add_user_cap_label');
        $language->addKeyForJs('admin', 'admin_suspend_floatbox_title');
        OW::getLanguage()->addKeyForJs('admin', 'are_you_sure');

        $menu = $this->getMenu();
        $this->addComponent('menu', $menu);

        if ( !empty($_GET['search']) && !empty($_GET['search_by']) )
        {
            $extra = array('question' => $_GET['search_by'], 'value' => $_GET['search']);
            $type = 'search';
        }
        else
        {
            $extra = null;
            $type = isset($params['list']) ? $params['list'] : 'recent';
        }
        
        $buttons['suspend'] = array('name' => 'suspend', 'id' => 'suspend_user_btn', 'label' => $language->text('base', 'suspend_user_btn'), 'class' => 'ow_mild_red');
        $buttons['suspend']['js'] = ' $("#suspend_user_btn").click(function(e){ 
            e.preventDefault();
            OW.ajaxFloatBox("ADMIN_CMP_SetSuspendMessage", [],{width: 520, title: OW.getLanguageText(\'admin\', \'admin_suspend_floatbox_title\')}); 
            return false;
        }); ';
        
        $buttons['unverify'] = array('name' => 'email_unverify', 'id' => 'email_unverify_user_btn', 'label' => $language->text('base', 'mark_email_unverified_btn'), 'class' => 'ow_mild_red');
        $buttons['unsuspend'] = array('name' => 'reactivate', 'id' => 'unsuspend_user_btn', 'label' => $language->text('base', 'unsuspend_user_btn'), 'class' => 'ow_mild_green');
        $buttons['verify'] = array('name' => 'email_verify', 'id' => 'email_verify_user_btn', 'label' => $language->text('base', 'mark_email_verified_btn'), 'class' => 'ow_mild_green');
        $buttons['approve'] = array('name' => 'approve', 'id' => 'approve_user_btn', 'label' => $language->text('base', 'approve_user_btn'), 'class' => 'ow_mild_green');
        //$buttons['disapprove'] = array('name' => 'disapprove', 'id' => 'disapprove_user_btn', 'label' => $language->text('base', 'disapprove_user_btn'), 'class' => 'ow_mild_red');
        $par = new ADMIN_UserListParams();
        $par->setType($type);
        $par->setExtra($extra);
        $frmsmsEvent = OW::getEventManager()->trigger(new OW_Event('frmsms.add.activate.sms.code.button',array('type' =>$type)));
        if(isset($frmsmsEvent->getData()['buttonSMSActivation'])){
            $par->addButton($frmsmsEvent->getData()['buttonSMSActivation']);
        }else {
            switch ($type) {
                case 'recent';
                case 'search':
                    $par->addButton($buttons['suspend']);
                    $par->addButton($buttons['unsuspend']);
                    $par->addButton($buttons['unverify']);
                    $par->addButton($buttons['verify']);
                    $par->addButton($buttons['approve']);
                    //$par->addButton($buttons['disapprove']);
                    break;

                case 'suspended':
                    $par->addButton($buttons['unsuspend']);
                    break;

                case 'unverified':
                    $par->addButton($buttons['verify']);
                    break;

                case 'unapproved':
                    $par->addButton($buttons['approve']);
                    break;
            }
        }


        if(OW::getPluginManager()->isPluginActive('frmuserlogin')){
            $buttons['terminate_devices'] = array('name' => 'terminate_devices', 'id' => 'terminate_devices', 'label' => $language->text('frmuserlogin', 'terminate_all_devices'), 'class' => 'ow_mild_red');
            $par->addButton($buttons['terminate_devices']);
        }

        $usersCmp = OW::getClassInstance("ADMIN_CMP_UserList", $par);
        $this->addComponent('userList', $usersCmp);

        if ( !OW::getRequest()->isAjax() )
        {
            OW::getDocument()->setHeading(OW::getLanguage()->text('admin', 'heading_browse_users'));
            OW::getDocument()->setHeadingIconClass('ow_ic_user');

            $menuElement = $menu->getElement($type);
            if ( $menuElement )
            {
                $menuElement->setActive(true);
            }
        }
        
        $this->assign('totalUsers', BOL_UserService::getInstance()->count(true));
        
        $question = OW::getConfig()->getValue('base', 'display_name_question');
        
        $searchQ = array(
            $question => $language->text('base', 'questions_question_'.$question.'_label'),
            'email' => $language->text('base', 'questions_question_email_label')
        );

        $eventSms = OW::getEventManager()->trigger(new OW_Event('on.get.searchQ.admin',['searchQ'=>$searchQ]));
        if(isset($eventSms->getData()['searchQ']))
        {
            $searchQ = $eventSms->getData()['searchQ'];
        }
        $this->assign('searchQ', $searchQ);
        
        $this->assign('currentSearch', array(
            'question' => !empty($_GET['search_by']) ? $_GET['search_by'] : '',
            'value' => !empty($_GET['search']) ? htmlspecialchars($_GET['search']) : ''
        ));
        
        $this->assign('userSearchUrl', OW::getRouter()->urlForRoute('admin_users_browse'));
        if(OW::getPluginManager()->isPluginActive('frmsecurityessentials'))
            $this->assign('changePasswordUrl', OW::getRouter()->urlForRoute('frmsecurityessentials.admin.currentSection', array('currentSection' => 4)));
    }

    public function roles( array $params )
    {        
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-ui.min.js');

        $service = BOL_UserService::getInstance();

        $roleService = BOL_AuthorizationService::getInstance();

        $roles = $roleService->findNonGuestRoleList();
       

        $list = array();

        $total = $service->count(true);

        foreach ( $roles as $role )
        {            
            $userCount = $roleService->countUserByRoleId($role->getId());           

            $list[$role->getId()] = array(
                'dto' => $role,
                'userCount' => $userCount,
            );
        }
        
        $this->assign( 'set', $list );

        $this->assign( 'total', $total );

        $addRoleForm = new AddRoleForm();

        if ( OW::getRequest()->isPost() && $addRoleForm->isValid( $_POST ) )
        {
            $addRoleForm->process($addRoleForm->getValues());
            sleep(2);
            $this->redirect();
        }

        $this->addForm( $addRoleForm );
        
        OW::getLanguage()->addKeyForJs('admin', 'permissions_edit_role_btn');

        OW::getDocument()->setHeadingIconClass('ow_ic_user');
        OW::getDocument()->setHeading(OW::getLanguage()->text('admin', 'heading_user_roles'));
        
        // users roles
        $service = BOL_AuthorizationService::getInstance();
        $this->assign('formAction', OW::getRouter()->urlFor('ADMIN_CTRL_Permissions', 'savePermissions'));

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
        $this->assign('colspanForRoles', count($roles) + 2);
        $this->assign('groupActionList', $groupActionList);
        $this->assign('guestRoleId', $service->getGuestRoleId());

        // SD code below - collecting group labels
        $event = new BASE_CLASS_EventCollector('admin.add_auth_labels');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        $dataLabels = empty($data) ? array() : call_user_func_array('array_merge', $data);
        $this->assign('labels', $dataLabels);

        $this->setDocumentKey("user_roles");
    }

    public function role( array $params )
    {
        if ( !empty($params['roleId']) )
        {
            $par = new ADMIN_UserListParams();
            $par->setType('role');
            $par->setExtra(array('roleId' => (int) $params['roleId']));
            
            $this->addComponent('userList', new ADMIN_CMP_UserList($par));

            $role = BOL_AuthorizationService::getInstance()->getRoleById((int) $params['roleId']);
            $roleLabel = OW::getLanguage()->text('base', 'authorization_role_' . $role->name);

            OW::getDocument()->setHeading(OW::getLanguage()->text('admin', 'heading_user_role', array('role' => $roleLabel)));
        }

        OW::getDocument()->setHeadingIconClass('ow_ic_user');

        $js = UTIL_JsGenerator::newInstance()
                ->newVariable('rolesUrl', OW::getRouter()->urlForRoute('admin_user_roles'))
                ->jQueryEvent('#back-to-roles', 'click', 'document.location.href = rolesUrl');

        OW::getDocument()->addOnloadScript($js);
    }

    public function deleteRoles()
    {
        $service = BOL_AuthorizationService::getInstance();

        if (empty($_POST['role']) || !is_array($_POST['role']))
        {
            $this->redirect(OW::getRouter()->urlFor('ADMIN_CTRL_Users', 'roles'));
        }

        foreach ( $_POST['role'] as $id )
        {
            $service->deleteRoleById($id);
        }

        $languageService = BOL_LanguageService::getInstance();

        $languageService->generateCache($languageService->getCurrent()->getId());

        OW::getFeedback()->info(OW::getLanguage()->text('admin', 'permissions_roles_deleted_msg'));

        $this->redirect(OW::getRouter()->urlFor('ADMIN_CTRL_Users', 'roles'));
    }

    public function ajaxReorder()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        if ( empty($_POST) )
        {
            exit('{}');
        }

        BOL_AuthorizationService::getInstance()->reorderRoles($_POST['order']);
        exit();
    }
    
    public function ajaxEditRole( )
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }
        
        ADMIN_CMP_AuthorizationRoleEdit::process($_POST);
    }


    /**
     * Responder for add user form
     */
    public function addUserResponder()
    {
        if (!OW::getRequest()->isAjax() || !OW::getUser()->isAdmin()) {
            throw new Redirect404Exception();
        }
        $valid= true;
        $userNameValidator = new NewUserUsernameValidator();
        $userEmailValidator = new NewUserEmailValidator();
        if(!$userNameValidator->isValid($_POST['username']))
        {
            $resp['message'] = $userNameValidator->getError();
            $valid=false;
        }
        else if(!$userEmailValidator->isValid($_POST['email']))
        {
            $resp['message'] = $userEmailValidator->getError();
            $valid=false;
        }
        if($valid) {
            $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;

            $user = FRMSecurityProvider::createUser($_POST['username'], $_POST['email'], $_POST['password'], "1969/3/21", "1", $accountType, 'c0de');

            $resp['message'] = OW::getLanguage()->text('admin', 'user_added_msg');
            $resp['user_id'] = $user->id;
        }
        echo json_encode($resp);
        exit;
    }
}

class AddRoleForm extends Form
{

    public function __construct()
    {
        parent::__construct('add-role');

        $textField = new TextField('label');

        $this->addElement($textField->setRequired(true)->setLabel(OW::getLanguage()->text('admin', 'permissions_add_form_role_lbl')));

        $submit = new Submit('submit');

        $submit->setValue(OW::getLanguage()->text('admin', 'permissions_add_role_btn'));

        $this->addElement($submit);
    }

    public function process( $data )
    {
        $label = $data['label'];

        $service = BOL_AuthorizationService::getInstance();

        $service->addRole($label);

        OW::getFeedback()->info(OW::getLanguage()->text('admin', 'permissions_role_added_msg'));
    }
}


class AddUserForm extends Form
{

    public function __construct()
    {
        parent::__construct('add-user');
        $this->setAjax();
        $this->reset();
        $this->setAction(OW::getRouter()->urlFor('ADMIN_CTRL_Users', 'addUserResponder'));
        $usernameField = new TextField('username');
        $usernameField->addValidator(new NewUserUsernameValidator());
        $usernameField->setRequired();
        $usernameField->addAttribute('autocomplete','new-password');
        $usernameField->setLabel(OW::getLanguage()->text('admin', 'admin_user_username'));
        $this->addElement($usernameField);

        $passwordField = new PasswordField('password');
        $passwordField->addAttribute('autocomplete','new-password');
        $passwordField->setRequired();
        $passwordField->setLabel(OW::getLanguage()->text('admin', 'admin_user_password'));
        $this->addElement($passwordField);

        $emailField = new TextField('email');
        $emailField->setRequired();
        $emailField->addValidator(new NewUserEmailValidator());
        $emailField->setLabel(OW::getLanguage()->text('admin', 'admin_user_email'));
        $this->addElement($emailField);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('admin', 'add_user_btn'));
        $this->addElement($submit);
    }
}
