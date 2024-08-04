<?php
/**
 * User list component class. 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.admin.components
 * @since 1.0
 */
class ADMIN_CMP_UserList extends OW_Component
{    
    /**
     * Constructor.
     * 
     * @param string $type
     * @param array $extra
     */
    public function __construct( ADMIN_UserListParams $params )
    {
        parent::__construct();
        
        $language = OW::getLanguage();
        $userService = BOL_UserService::getInstance();
        $authService = BOL_AuthorizationService::getInstance();

        $type = $params->getType();
        $extra = $params->getExtra();
        $formAction = $params->getAction();
        $this->assign('action', $formAction);
        
        // handle form
        if ( OW::getRequest()->isPost() && !empty($_POST['users']) )
        {
            $users = $_POST['users'];

            if ( isset($_POST['suspend']) )
            {
                foreach ( $users as $id )
                {
                    // admin user cannot be suspended
                    if ( $authService->isActionAuthorizedForUser($id, BOL_AuthorizationService::ADMIN_GROUP_NAME) )
                    {
                        continue;
                    }
                    
                    $userService->suspend($id, $_POST['suspend_message']);
                }

                OW::getFeedback()->info($language->text('admin', 'user_feedback_profiles_suspended'));
            }
            else if ( isset($_POST['reactivate']) )
            {
                foreach ( $users as $id )
                {
                    $userService->unsuspend($id);
                }

                OW::getFeedback()->info($language->text('admin', 'user_feedback_profiles_unsuspended'));                
            }
            else if ( isset($_POST['delete']) )
            {
                $newDeletePath = OW::getEventManager()->trigger(new OW_Event('base.before.action_user_delete', array('href' => '', 'users' => $users)));
                if(isset($newDeletePath->getData()['href'])){
                    $href = $newDeletePath->getData()['href'];
                    OW::getApplication()->redirect($href);
                    exit();
                }

                $deleted = 0;

                foreach ( $users as $id )
                {
                    // admin user cannot be deleted
                    if ( $authService->isActionAuthorizedForUser($id, BOL_AuthorizationService::ADMIN_GROUP_NAME) )
                    {
                        continue;
                    }

                    if ( $userService->deleteUser($id, true) )
                    {
                        $deleted++;
                    }
                }

                OW::getFeedback()->info($language->text('admin', 'user_delete_msg', array('count' => $deleted)));
            }
            else if ( isset($_POST['email_verify']) )
            {
                $userDtos = $userService->findUserListByIdList($users);
                
                foreach ( $userDtos as $dto )
                {
                    /* @var $dto BOL_User */
                    $dto->emailVerify = 1;
                    $userService->saveOrUpdate($dto);
                }

                OW::getFeedback()->info($language->text('admin', 'user_feedback_email_verified'));
            }
            else if ( isset($_POST['email_unverify']) )
            {
                $userDtos = $userService->findUserListByIdList($users);
                
                foreach ( $userDtos as $dto )
                {
                    // admin user cannot be unverified
                    if ( $authService->isActionAuthorizedForUser($dto->id, BOL_AuthorizationService::ADMIN_GROUP_NAME) )
                    {
                        continue;
                    }
                    
                    /* @var $dto BOL_User */
                    $dto->emailVerify = 0;
                    $userService->saveOrUpdate($dto);
                }

                OW::getFeedback()->info($language->text('admin', 'user_feedback_email_unverified'));
            }
            else if ( isset($_POST['terminate_devices']) )
            {
                $userDtos = $userService->findUserListByIdList($users);

                foreach ( $userDtos as $dto )
                {
                    /* @var $dto BOL_User */
                    FRMUSERLOGIN_BOL_Service::getInstance()->terminateAllDevices($dto->id);
                }

                OW::getFeedback()->info($language->text('frmuserlogin', 'user_feedback_devices_terminated'));
            }
            else if ( isset($_POST['disapprove']) )
            {
                foreach ( $users as $id )
                {
                    // admin user cannot be disapproved
                    if ( $authService->isActionAuthorizedForUser($id, BOL_AuthorizationService::ADMIN_GROUP_NAME) )
                    {
                        continue;
                    }
                    
                    $userService->disapprove($id);
                    $this->sendEmailToUser($id, $language->text('base', 'email_user_not_approved_notifications'));
                }

                OW::getFeedback()->info($language->text('admin', 'user_feedback_profiles_disapproved'));
            }
            else if ( isset($_POST['approve']) )
            {
                foreach ( $users as $id )
                {
                    if ( !$userService->isApproved($id) )
                    {
                        $userService->approve($id);
                        $this->sendEmailToUser($id, $language->text('base', 'email_user_approved_notifications'));
                    }
                }

                OW::getFeedback()->info($language->text('admin', 'user_feedback_profiles_approved'));
            }
            $frmsmsEvent = OW::getEventManager()->trigger(new OW_Event('frmsms.activate.user.sms.code',array('postData'=>$_POST)));
            if(isset($frmsmsEvent->getData()['success'])){
                OW::getFeedback()->info($language->text('admin', 'user_feedback_profiles_approved'));
            }
            $this->reloadParentPage();
        }

        $onPage = 20;

        $page = isset($_GET['page']) && (int) $_GET['page'] ? (int) $_GET['page'] : 1;
        $first = ( $page - 1 ) * $onPage;
        $userList = null;
        $userCount = 0;
        switch ($type) {
            case 'recent':
                $userList = $userService->findRecentlyActiveList($first, $onPage, false);
                $userCount = $userService->count(false);
                break;

            case 'suspended':
                $userList = $userService->findSuspendedList($first, $onPage);
                $userCount = $userService->countSuspended();
                break;

            case 'unverified':
                $userList = $userService->findUnverifiedList($first, $onPage);
                $userCount = $userService->countUnverified();
                break;

            case 'unapproved':
                $userList = $userService->findUnapprovedList($first, $onPage);
                $userCount = $userService->countUnapproved();
                break;

            case 'search':
                if (isset($extra['question'])) {
                    $search = htmlspecialchars(urldecode($extra['value']));
                    $this->assign('search', $search);
                    $userList = $userService->findUserListByQuestionValues(array($extra['question'] => $search), $first, $onPage, true);
                    $userCount = $userService->countUsersByQuestionValues(array($extra['question'] => $search), true);
                }
                break;

            case 'role':
                $roleId = $extra['roleId'];
                $userList = $userService->findListByRoleId($roleId, $first, $onPage);
                $userCount = $userService->countByRoleId($roleId);
                break;

            case 'smsActivation':
                $getUnverifiedSMSUsersEvent = OW::getEventManager()->trigger(new OW_Event('frmsms.get.userlist.needs.activation',array('first'=>$first,'count'=>$onPage)));
                if(isset($getUnverifiedSMSUsersEvent->getData()['userCount']) && isset($getUnverifiedSMSUsersEvent->getData()['userList'])) {
                    $userList = $getUnverifiedSMSUsersEvent->getData()['userList'];
                    $userCount = $getUnverifiedSMSUsersEvent->getData()['userCount'];
                }
                break;
            }


        if ( !$userList && $page > 1 )
        {
            OW::getApplication()->redirect(OW::getRequest()->buildUrlQueryString(null, array('page' => $page - 1)));
        }
        
        if ( $userList )
        {
            $this->assign('users', $userList);
            $this->assign('total', $userCount);

            // Paging
            $pages = (int) ceil($userCount / $onPage);
            $paging = new BASE_CMP_Paging($page, $pages, $onPage);

            $this->addComponent('paging', $paging);

            $userIdList = array();

            foreach ( $userList as $user )
            {
                if ( !in_array($user->id, $userIdList) )
                {
                    array_push($userIdList, $user->id);
                }
            }

            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList);
            $this->assign('avatars', $avatars);
            
            $userNameList = $userService->getUserNamesForList($userIdList);
            $this->assign('userNameList', $userNameList);

            $fieldList = array('sex', 'birthdate', 'email');
            $eventFieldList = OW::getEventManager()->trigger(new OW_Event('on.admin.userlist.question.field.value',['fieldList'=>$fieldList]));
            if(isset($eventFieldList->getData()['fieldList']))
            {
                $fieldList = $eventFieldList->getData()['fieldList'];
            }
            $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $fieldList);
            $this->assign('questionList', $questionList);

            $sexList = array();
            
            foreach ( $userIdList as $id )
            {
                if ( empty($questionList[$id]['sex']) )
                {
                    
                    continue;
                }

                $sex = $questionList[$id]['sex'];

                if ( !empty($sex) )
                {
                    $sexValue = '';

                    for ( $i = 0 ; $i < 31; $i++ )
                    {
                        $val = pow( 2, $i );
                        if ( (int)$sex & $val  )
                        {
                            $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                        }
                    }

                    if ( !empty($sexValue) )
                    {
                        $sexValue = substr($sexValue, 0, -2);
                    }
                }

                $sexList[$id] = $sexValue;
            }
            
            $this->assign('sexList', $sexList);

            $userSuspendedList = $userService->findSupsendStatusForUserList($userIdList);
            $this->assign('suspendedList', $userSuspendedList);

            $userUnverfiedList = $userService->findUnverifiedStatusForUserList($userIdList);
            $this->assign('unverifiedList', $userUnverfiedList);

            $userUnapprovedList = $userService->findUnapprovedStatusForUserList($userIdList);
            $this->assign('unapprovedList', $userUnapprovedList);

            $onlineStatus = $userService->findOnlineStatusForUserList($userIdList);
            $this->assign('onlineStatus', $onlineStatus);


            $findUnverifiedStatusSMSEvent = OW::getEventManager()->trigger(new OW_Event('frmsms.find.unverified.status.for.user.list',array('userIdList'=>$userIdList)));
            if(isset($findUnverifiedStatusSMSEvent->getData()['userUnverifiedSMSList'])) {
                $userUnverifiedSMSList = $findUnverifiedStatusSMSEvent->getData()['userUnverifiedSMSList'];
                $this->assign('userUnverifiedSMSList', $userUnverifiedSMSList);
            }


            $moderatorList = $authService->getModeratorList();
            $adminList = array();
            
            /* @var $moderator BOL_AuthorizationModerator */
            foreach ( $moderatorList as $moderator )
            {
                $userId = $moderator->getUserId();
                if ( $userService->findUserById($userId) !== null && $authService->isActionAuthorizedForUser($userId, BOL_AuthorizationService::ADMIN_GROUP_NAME) )
                {
                    $adminList[] = $userId;
                }
            }
            $this->assign('adminList', $adminList);
        }
        else
        {
            $this->assign('users', null);
        }

        $this->assign('adminId', OW::getUser()->getId());
        $this->assign('buttons', $params->getButtons());
        
        $script = '$("#check-all").click(function() {
            $("#user-list-form input:not([disabled])[type=checkbox]").prop("checked", $(this).prop("checked"));
;
        });';

        OW::getDocument()->addOnloadScript($script);
    }

    private function reloadParentPage()
    {
        $router = OW::getRouter();

        OW::getApplication()->redirect(OW::getRequest()->buildUrlQueryString());
    }

    private function sendEmailToUser($userId, $text) {
        $subject = UTIL_HtmlTag::stripTagsAndJs(OW::getLanguage()->text('base','email_user_approved_title'));
        $message = UTIL_HtmlTag::stripTagsAndJs($text);
        $user = BOL_UserService::getInstance()->findUserById($userId);

        $from = OW::getConfig()->getValue('base', 'site_email');

        $mail = OW::getMailer()->createMail();
        $mail->addRecipientEmail($user->getEmail());
        $mail->setSender($from);
        $mail->setSenderSuffix(false);
        $mail->setSubject($subject);
        $mail->setTextContent($message);
        $mail->setHtmlContent($message);
        OW::getMailer()->addToQueue($mail);
    }
}

final class ADMIN_UserListParams
{
    private $action;
    
    private $type;
    
    private $buttons = array();
    
    private $extra = array();
    
    public function __construct() 
    {
        $lang = OW::getLanguage();
        
        $this->buttons['delete'] = array('name' => 'delete', 'id' => 'delete_user_btn', 'label' => $lang->text('base', 'delete'), 'class' => 'ow_mild_red');
        $this->buttons['delete']['js'] = '$("#delete_user_btn").click(function(){
            
            var $form_content = $("#delete-user-confirm").children();
    
            window.delete_user_floatbox = new OW_FloatBox({
                $title: '.json_encode($lang->text('base', 'delete_user_confirmation_label')).',
                $contents: $form_content,
                icon_class: "ow_ic_delete",
                width: 450
            });
            
            return false;
        });
        
        $("#button-confirm-user-delete").click(function(){
            var $form = $("#user-list-form");
            $form.append("<input type=\"hidden\" name=\"delete\" value=\"Delete\" />");
            $form.submit();
        });';
    }
    
    public function addButton( array $button )
    {
        $this->buttons[$button['name']] = $button;
    }
    
    public function setAction( $action )
    {
        $this->action = $action;
    }
    
    public function setType( $type )
    {
        $this->type = $type;
    }
    
    public function setExtra( $extra )
    {
        $this->extra = $extra;
    }
    
    public function getButtons()
    {
        return $this->buttons;
    }
    
    public function getAction()
    {
        return $this->action;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function getExtra()
    {
        return $this->extra;
    }
}