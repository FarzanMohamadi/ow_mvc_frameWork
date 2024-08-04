<?php
/**
 * Edit user details
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_Edit extends OW_ActionController
{
    const EDIT_SYNCHRONIZE_HOOK = 'edit_synchronize_hook';
    const PREFERENCE_LIST_OF_CHANGES = 'base_questions_changes_list';

    private $questionService;
    private $userService;

    public function __construct()
    {
        parent::__construct();

        $this->questionService = BOL_QuestionService::getInstance();
        $this->userService = BOL_UserService::getInstance();

        $preference = BOL_PreferenceService::getInstance()->findPreference(self::PREFERENCE_LIST_OF_CHANGES);

        if ( empty($preference) )
        {
            $preference = new BOL_Preference();
        }

        $preference->key = self::PREFERENCE_LIST_OF_CHANGES;
        $preference->defaultValue = json_encode(array());
        $preference->sectionName = 'general';
        $preference->sortOrder = 100;

        BOL_PreferenceService::getInstance()->savePreference($preference);
    }

    public function index( $params )
    {
        $adminMode = false;
        $viewerId = OW::getUser()->getId();

        if (!OW::getUser()->isAuthenticated() || $viewerId === null) {
            throw new AuthenticateException();
        }

        if (!empty($params['userId']) && $params['userId'] != $viewerId) {

            if (OW::getUser()->isAuthorized('base','edit_user_profile')) {
                $adminMode = true;
                $userId = (int)$params['userId'];
                $user = BOL_UserService::getInstance()->findUserById($userId);

                if (empty($user) || BOL_AuthorizationService::getInstance()->isSuperModerator($userId)) {
                    throw new Redirect404Exception();
                }

                $editUserId = $userId;
            } else {
                throw new Redirect404Exception();
            }
        } else {
            $editUserId = $viewerId;

            $changePassword = new BASE_CMP_ChangePassword();
            $this->addComponent("changePassword", $changePassword);
            OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_CHANGE_PASSWORD_WIDGET_ADDED, array('component' => $this)));
            $contentMenu = new BASE_CMP_DashboardContentMenu();
            $contentMenu->setItemActive('profile_edit');

            $this->addComponent('contentMenu', $contentMenu);

            $user = OW::getUser()->getUserObject(); //BOL_UserService::getInstance()->findUserById($editUserId);
        }

        $changeList = BOL_PreferenceService::getInstance()->getPreferenceValue(self::PREFERENCE_LIST_OF_CHANGES, $editUserId);

        if (empty($changeList)) {
            $changeList = '[]';
        }

        $this->assign('changeList', json_decode($changeList, true));

        $isEditedUserModerator = BOL_AuthorizationService::getInstance()->isModerator($editUserId) || BOL_AuthorizationService::getInstance()->isSuperModerator($editUserId);

        $accountType = $user->accountType;

        $userCanChangeAccountType = new OW_Event(FRMSECURITYESSENTIALS_BOL_Service::CHECK_USER_CAN_CHANGE_ACCOUNT_TYPE,
            array(
                'editUserId' => $editUserId,
                'viewerId' => $viewerId
            ));
        OW::getEventManager()->trigger($userCanChangeAccountType);
        $userCanChangeAccountType = isset($userCanChangeAccountType->getData()['user_can_change_account_type']) ?
                                    $userCanChangeAccountType->getData()['user_can_change_account_type'] :
                                    false;

        // display account type
        if (OW::getUser()->isAdmin() || OW::getUser()->isAuthorized('base', 'edit_user_profile') || $userCanChangeAccountType) {
            $accountType = !empty($_GET['accountType']) ? $_GET['accountType'] : $user->accountType;

            // get available account types from DB
            $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();

            $accounts = array();

            if (count($accountTypes) > 1) {
                /* @var $value BOL_QuestionAccount */
                foreach ($accountTypes as $key => $value) {
                    $accounts[$value->name] = OW::getLanguage()->text('base', 'questions_account_type_' . $value->name);
                }

                if (!in_array($accountType, array_keys($accounts))) {
                    if (in_array($user->accountType, array_keys($accounts))) {
                        $accountType = $user->accountType;
                    } else {
                        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
                    }
                }

                $editAccountType = new Selectbox('accountType');
                $editAccountType->setId('accountType');
                $editAccountType->setLabel(OW::getLanguage()->text('base', 'questions_question_account_type_label'));
                $editAccountType->setRequired();
                $editAccountType->setOptions($accounts);
                $editAccountType->setHasInvitation(false);
            } else {
                $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
            }
        }

        $language = OW::getLanguage();
        $displayName = BOL_UserService::getInstance()->getDisplayName($editUserId);
        $this->setPageHeading($language->text('base', 'edit_index'));
        $this->setPageHeadingIconClass('ow_ic_user');
        $this->assign("displayName", $displayName);
        // -- Edit form --

        $editForm = new EditQuestionForm('editForm', $editUserId);
        $editForm->setId('editForm');

        $this->assign('displayAccountType', false);

        // display account type
        if (!empty($editAccountType)) {
            $editAccountType->setValue($accountType);
            $editAccountType->setDescription(BOL_QuestionService::getInstance()->getQuestionDescriptionLang($editAccountType->getName()));
            $editForm->addElement($editAccountType);

            OW::getDocument()->addOnloadScript(" $('#accountType').change(function() {
                
                var form = $(\"<form method='get'><input type='text' name='accountType' value='\" + $(this).val() + \"' /></form>\");
                $('body').append(form);
                $(form).submit();

            }  ); ");

            $this->assign('displayAccountType', true);
        }

        $userId = !empty($params['userId']) ? $params['userId'] : $viewerId;

        $this->assign('profileUrl',  BOL_UserService::getInstance()->getUserUrl(OW::getUser()->getId()));

        $isUserApproved = BOL_UserService::getInstance()->isApproved($editUserId);
        $this->assign('isUserApproved', $isUserApproved);

        if(!$isUserApproved && OW::getConfig()->getValue('base', 'mandatory_user_approve')){
            $moderator_note = BOL_UserApproveDao::getInstance()->getRequestedNotes($userId);
            if (!empty($moderator_note)){
                $note = $moderator_note['admin_message'];
                $note = str_replace("\n", '<br />', $note);
                $this->assign('moderator_note', $note);
            }
        }

        // password required
        $this->assign('passwordRequiredProfile', false);
        if(OW::getConfig()->configExists('frmsecurityessentials','passwordRequiredProfile')){
            $passwordRequiredProfile=OW::getConfig()->getValue('frmsecurityessentials','passwordRequiredProfile');
            if($passwordRequiredProfile){
                $password = BOL_UserService::getInstance()->getOldPasswordInput('oldPasswordCheck', $editForm->getName());
                $password->setDescription(BOL_QuestionService::getInstance()->getQuestionDescriptionLang($password->getName()));
                $editForm->addElement($password);
                $this->assign('passwordRequiredProfile', true );
            }
        }

        // add submit button
        $editSubmit = new Submit('editSubmit');
        $editSubmit->addAttribute('class', 'ow_ic_save');

        $editSubmit->setValue($language->text('base', 'edit_data_button'));

        if ($adminMode && !$isUserApproved) {
            $editSubmit->setName('saveAndApprove');
            $editSubmit->setValue($language->text('base', 'save_and_approve'));

            // TODO: remove
            if (!$isEditedUserModerator) {
                // add delete button
                $script = UTIL_JsGenerator::newInstance()->jQueryEvent('input.delete_user_by_moderator', 'click', 'OW.Users.deleteUser(e.data.userId, e.data.callbackUrl, false);'
                    , array('e'), array('userId' => $userId, 'callbackUrl' => OW::getRouter()->urlForRoute('base_member_dashboard')));
                OW::getDocument()->addOnloadScript($script);
            }
        }

        // add cancel button
        $editUserUsername = BOL_UserService::getInstance()->getUserName($editUserId);
        $cancelUrl = OW::getRouter()->urlForRoute('base_user_profile', array('username' => $editUserUsername));
        $this->assign("cancelUrl", $cancelUrl);

        $editSubmit->setDescription(BOL_QuestionService::getInstance()->getQuestionDescriptionLang($editSubmit->getName()));
        $editForm->addElement($editSubmit);

        // prepare question list
        $questions = $this->questionService->findEditQuestionsForAccountType($accountType);
        $onBeforeProfileEditFormBuildEventResults = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PROFILE_EDIT_FORM_BUILD, array('questions' => $questions)));
        if(isset($onBeforeProfileEditFormBuildEventResults->getData()['questions'])){
            $questions = $onBeforeProfileEditFormBuildEventResults->getData()['questions'];
        }
        $section = null;
        $questionArray = array();
        $questionNameList = array();

        foreach ($questions as $sort => $question) {
            if ($section !== $question['sectionName']) {
                $section = $question['sectionName'];
            }

            $questionArray[$section][$sort] = $questions[$sort];
            $questionNameList[] = $questions[$sort]['name'];
        }

        $this->assign('questionArray', $questionArray);

        $questionData = $this->questionService->getQuestionData(array($editUserId), $questionNameList);
        $eventQuestionData = new OW_Event('change.edit.question.data', array(
            'questionData' => $questionData[$userId]
        ));
        OW::getEventManager()->trigger($eventQuestionData);
        if(isset($eventQuestionData->getData()['questionData']))
        {
            $questionData[$userId]=$eventQuestionData->getData()['questionData'];
        }
        $questionValues = $this->questionService->findQuestionsValuesByQuestionNameList($questionNameList);
        // add question to form
        $editForm->addQuestions($questions, $questionValues, !empty($questionData[$editUserId]) ? $questionData[$editUserId] : array());
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_JOIN_FORM_RENDER, array('form' => $editForm, 'controller' => $this,'forEditProfile' =>true, 'editUserId' => $editUserId)));
        // process form
        if (OW::getRequest()->isPost()) {
            if (isset($_POST['editSubmit']) || isset($_POST['saveAndApprove'])) {
                $this->process($editForm, $user->id, $questionArray, $adminMode);
            }
        }
        $this->addForm($editForm);

        $deleteUrl = OW::getRouter()->urlForRoute('base_delete_user');

        $this->assign('unregisterProfileUrl', $deleteUrl);

        // add langs to js
        $language->addKeyForJs('base', 'join_error_username_not_valid');
        $language->addKeyForJs('base', 'join_error_username_already_exist');
        $language->addKeyForJs('base', 'join_error_email_not_valid');
        $language->addKeyForJs('base', 'join_error_email_already_exist');
        $language->addKeyForJs('base', 'join_error_password_not_valid');
        $language->addKeyForJs('base', 'join_error_password_too_short');
        $language->addKeyForJs('base', 'join_error_password_too_long');
        $language->addKeyForJs('base', 'reset_password_not_equal_error_message');

        //include js
        $onLoadJs = " window.edit = new OW_BaseFieldValidators( " .
            json_encode(array(
                'formName' => $editForm->getName(),
                'responderUrl' => OW::getRouter()->urlFor("BASE_CTRL_Edit", "ajaxResponder"))) . ",
                                                        " . UTIL_Validator::EMAIL_PATTERN . ", " . UTIL_Validator::USER_NAME_PATTERN . ", " . $editUserId . " ); ";

        $this->assign('isAdmin', OW::getUser()->isAdmin());
        $this->assign('isEditedUserModerator', $isEditedUserModerator);
        $this->assign('adminMode', $adminMode);
        $approveEnabled = OW::getConfig()->getValue('base', 'mandatory_user_approve');
        $userDisapprove = new OW_Event(FRMEventManager::ON_BEFORE_USER_DISAPPROVE_AFTER_EDIT_PROFILE, array(
            'checkApproveEnabled' => true,
        ));
        OW::getEventManager()->trigger($userDisapprove);
        if(isset($userDisapprove->getData()['approveEnabled'])){
            $approveEnabled = $userDisapprove->getData()['approveEnabled'];
        }

        $this->assign('approveEnabled', $approveEnabled);

        OW::getDocument()->addOnloadScript('
            $("input.write_message_button").click( function() {
                    OW.ajaxFloatBox("BASE_CMP_SendMessageToEmail", [' . ((int)$editUserId) . '],
                    {
                        title: ' . json_encode($language->text('base', 'send_message_to_email')) . ',
                        width:600
                    });
                }
            );
        ');

        OW::getDocument()->addOnloadScript($onLoadJs);

        $jsDir = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "base_field_validators.js");

        if (!$adminMode) {
            $editSynchronizeHook = OW::getRegistry()->getArray(self::EDIT_SYNCHRONIZE_HOOK);

            if (!empty($editSynchronizeHook)) {
                $content = array();

                foreach ($editSynchronizeHook as $function) {
                    $result = call_user_func($function);

                    if (trim($result)) {
                        $content[] = $result;
                    }
                }

                $content = array_filter($content, 'trim');

                if (!empty($content)) {
                    $this->assign('editSynchronizeHook', $content);
                }
            }
        }

        $this->setDocumentKey("profile_edit");
    }

    private function process($editForm, $userId, $questionArray, $adminMode)
    {
        if ( $editForm->isValid($_POST) )
        {
            $language = OW::getLanguage();
            $data = $editForm->getValues();
            $event = new OW_Event(OW_EventManager::ON_USER_REGISTER, array('userId' => $userId, 'method' => 'native', 'params' => $data,'forEditProfile'=>true));
            OW::getEventManager()->trigger($event);
            foreach ( $questionArray as $section )
            {
                foreach ( $section as $key => $question )
                {
                    switch ( $question['presentation'] )
                    {
                        case 'multicheckbox':

                            if ( is_array($data[$question['name']]) )
                            {
                                $answer = array();
                                foreach ($data[$question['name']] as $key => $value )
                                {
                                    $answer[] = (int)$value;
                                }
                                $data[$question['name']] = json_encode($answer);
                                //$data[$question['name']] = array_sum($data[$question['name']]);
                            }
                            else
                            {
                                $data[$question['name']] = json_encode(array());
                            }

                            break;
                    }
                }
            }
            // save user data
            if ( !empty($userId) )
            {
                $changesList = $this->questionService->getChangedQuestionList($data, $userId);
                if ( $this->questionService->saveQuestionsData($data, $userId) )
                {
                    if ( !$adminMode )
                    {
                        $isNeedToModerate = $this->questionService->isNeedToModerate($changesList);
                        $event = new OW_Event(OW_EventManager::ON_USER_EDIT, array('userId' => $userId, 'method' => 'native', 'moderate' => $isNeedToModerate) );
                        OW::getEventManager()->trigger($event);

                        // saving changed fields
                        if ( BOL_UserService::getInstance()->isApproved($userId) )
                        {
                            $changesList = array();
                        }
                        elseif ( OW::getConfig()->getValue('base', 'mandatory_user_approve') && OW::getUser()->getId()==$userId) {
                            // applying requested changes from moderators
                            BOL_UserApproveDao::getInstance()->fixedRequestForChange($userId);
                            OW::getEventManager()->trigger(new OW_Event('base.mandatory_user_approve.edit', array('userId' => $userId)));
                            OW::getFeedback()->error(OW::getLanguage()->text('base', 'wait_for_approval'));
                            OW::getUser()->logout();
                            $this->redirect(OW_URL_HOME);
                        }

                        BOL_PreferenceService::getInstance()->savePreferenceValue(self::PREFERENCE_LIST_OF_CHANGES, json_encode($changesList), $userId);
                        // ----
                        $approveUserAfterEditProfile = OW::getConfig()->getValue('frmsecurityessentials', 'approveUserAfterEditProfile');
                        $needApprove = false;
                        if ($approveUserAfterEditProfile == null || $approveUserAfterEditProfile == 0 || $approveUserAfterEditProfile == false) {
                            $needApprove = true;
                        }
                        if ( OW::getConfig()->getValue('base', 'mandatory_user_approve') && OW::getUser()->isAuthenticated() && $needApprove && !OW::getUser()->isAdmin()) {
                            OW::getEventManager()->trigger(new OW_Event('base.mandatory_user_approve.edit', array('userId' => $userId)));
                            OW::getFeedback()->error(OW::getLanguage()->text('base', 'wait_for_approval'));
                            OW_User::getInstance()->logout();
                            $this->redirect(OW_URL_HOME);
                        }
                        else {
                            OW::getFeedback()->info($language->text('base', 'edit_successfull_edit'));
                            $this->redirect(OW::getRouter()->urlForRoute('base_user_profile', array('username' => BOL_UserService::getInstance()->getUserName($userId))));
                        }
                    }
                    else
                    {
                        $event = new OW_Event(OW_EventManager::ON_USER_EDIT_BY_ADMIN, array('userId' => $userId));
                        OW::getEventManager()->trigger($event);

                        BOL_PreferenceService::getInstance()->savePreferenceValue(self::PREFERENCE_LIST_OF_CHANGES, json_encode(array()), $userId);

                        if ( !BOL_UserService::getInstance()->isApproved($userId) )
                        {
                            BOL_UserService::getInstance()->approve($userId);
                        }

                        OW::getFeedback()->info($language->text('base', 'edit_successfull_edit'));
                        $this->redirect(OW::getRouter()->urlForRoute('base_user_profile', array('username' => BOL_UserService::getInstance()->getUserName($userId))));
                    }
                }
                else
                {
                    OW::getFeedback()->info($language->text('base', 'edit_edit_error'));
                }
            }
            else
            {
                OW::getFeedback()->info($language->text('base', 'edit_edit_error'));
            }
        }
    }

    public function ajaxResponder()
    {
        $adminMode = false;

        if ( empty($_POST["command"]) || !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $editorId = OW::getUser()->getId();

        if ( !OW::getUser()->isAuthenticated() || $editorId === null )
        {
            throw new AuthenticateException(); // TODO: Redirect to login page
        }

        $editedUserId = $editorId;
        if ( !empty($_POST["userId"]) )
        {
            $adminMode = true;

            $userId = (int) $_POST["userId"];
            $user = $this->userService->findUserById($userId);

            if ( empty($user) )
            {
                echo json_encode(array('result' => false));
                exit;
            }

            if ( !OW::getUser()->isAdmin() && ( !OW::getUser()->isAuthorized('base') || (string)$_POST["command"] == 'validatePassword' ) && ( !OW::getUser()->isAuthenticated() || OW::getUser()->getId() != $userId ) )
            {
                echo json_encode(array('result' => false));
                exit;
            }

            $editedUserId = $user->id;
        }

        $command = (string) $_POST["command"];

        switch ( $command )
        {
            case 'isExistEmail':

                $email = $_POST["value"];

                $validator = new EditEmailValidator($editedUserId);
                $result = $validator->isValid($email);

                echo json_encode(array('result' => $result));

                break;

            case 'validatePassword':

                $result = false;

                if ( !$adminMode )
                {
                    $password = $_POST["value"];

                    $result = $this->userService->isValidPassword(OW::getUser()->getId(), $password);
                }

                echo json_encode(array('result' => $result));

                break;

            case 'isExistUserName':
                $username = $_POST["value"];

                $validator = new EditUserNameValidator($editedUserId);
                $result = $validator->isValid($username);

                echo json_encode(array('result' => $result));

                break;

            default:
        }
        exit();
    }
}

class EditQuestionForm extends BASE_CLASS_UserQuestionForm
{
    private $userId = null;

    public function __construct( $name, $userId = null )
    {
        parent::__construct($name);

        if ( $userId != null )
        {
            $this->userId = $userId;
        }
    }

    /**
     * Set field validator
     *
     * @param FormElement $formField
     * @param array $question
     */
    protected function addFieldValidator( $formField, $question )
    {
        if ( (string) $question['base'] === '1' )
        {
            if ( $question['name'] === 'email' )
            {
                $formField->addValidator(new EditEmailValidator($this->userId));
            }
            else if ( $question['name'] === 'username' )
            {
                $formField->addValidator(new EditUserNameValidator($this->userId));
            }
        }

        return $formField;
    }
}

