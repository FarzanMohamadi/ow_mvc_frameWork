<?php
/**
 * frmquestionroles
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmquestionroles
 * @since 1.0
 */

class FRMQUESTIONROLES_BOL_Service
{
    private static $classInstance;
    
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private $questionRolesDao;
    
    private function __construct()
    {
        $this->questionRolesDao = FRMQUESTIONROLES_BOL_QuestionRolesDao::getInstance();
    }

    /***
     * @return array
     */
    public function findAllRoles() {
        return $this->questionRolesDao->findAll();
    }

    /***
     * @param $id
     */
    public function deleteQuestionRole($id) {
        $this->questionRolesDao->deleteById($id);
    }

    public function getDisApprovedUsers($first = 0, $count = 21) {
        $currentQuestionsUserRoles = null;
        $viewAllUsers = false;
        if (OW::getUser()->isAuthenticated()) {
            if (OW::getUser()->isAdmin()) {
                $viewAllUsers = true;
            } else {
                $isAdmin = (BOL_AuthorizationService::getInstance()->isActionAuthorizedForUser(OW::getUser()->getId(), BOL_AuthorizationService::ADMIN_GROUP_NAME));
                if ($isAdmin) {
                    $viewAllUsers = true;
                } else {
                    $isBaseAdmin = (BOL_AuthorizationService::getInstance()->isActionAuthorizedForUser(OW::getUser()->getId(), 'base'));
                    if ($isBaseAdmin) {
                        $viewAllUsers = true;
                    }
                }
            }
            if (!$viewAllUsers) {
                $currentQuestionsUserRoles = $this->getUserRolesToManageSpecificUsers(OW::getUser()->getId());
            }
        }

        if ($currentQuestionsUserRoles == null && !$viewAllUsers) {
            return array('valid' => false);
        } else {
            $users = array();
            if ($viewAllUsers) {
                $usersObject = BOL_UserService::getInstance()->findUnapprovedList($first, $count);
                foreach ($usersObject as $userObject) {
                    $users[] = $userObject->id;
                }
                $allSize = BOL_UserService::getInstance()->countUnapproved();
            } else {
                $usersQuestionsData = $this->getUnapprovedUsersByRolesData($currentQuestionsUserRoles);
                $allUserIds = array();
                foreach ($usersQuestionsData as $userId => $userData) {
                    $allUserIds[] = $userId;
                }
                $ignoreUserIds = array();
                foreach ($usersQuestionsData as $userId => $userData) {
                    foreach ($currentQuestionsUserRoles as $questionRole) {
                        $qData = (array)json_decode($questionRole->data);
                        foreach ($qData as $key => $definedValue) {
                            if (isset($usersQuestionsData[$userId][$key])) {
                                $value = $usersQuestionsData[$userId][$key];
                                if ($value != $definedValue) {
                                    $ignoreUserIds[] = $userId;
                                }
                            }
                        }
                    }
                }
                $users = array_diff($allUserIds, $ignoreUserIds);
                $allSize = sizeof($users);
                $users = array_slice($users, $first, $count);
            }
            return array('valid' => true, 'users' => $users, 'allSize' => $allSize, 'hasLoadMore' => $allSize > $count);
        }
    }

    /***
     * @param null $userId
     * @return bool
     */
    public function hasAccessToRolesManagement($userId = null) {
        if ($userId == null && !OW::getUser()->isAuthenticated()) {
            return false;
        }
        if (OW::getUser()->isAdmin() || OW::getUser()->isAuthorized('frmquestionroles', 'manage_question_roles')) {
            return true;
        }
        return false;
    }

    public function getUserRoles($userId) {
        $aService = BOL_AuthorizationService::getInstance();
        $userRoles = $aService->findUserRoleList($userId);

        $userRolesIdList = array();
        foreach ( $userRoles as $role )
        {
            $userRolesIdList[] = $role->getId();
        }

        return $userRolesIdList;
    }

    /**
     * @param $definedValue
     * @param $value
     * @return bool
     */
    private function checkEqualAnswer($definedValue,$value)
    {
        if (is_array($definedValue) && is_array($value)) {
            $equalAnswer = array_keys($definedValue)[0] ==  array_keys($value)[0];
        }else if (is_array($definedValue)) {
            $equalAnswer = array_keys($definedValue)[0] ==  $value;
        }else if (is_array($value)) {
            $equalAnswer =  isset($value[$definedValue]);
        }else {
            $equalAnswer = $definedValue == $value;
        }
        return $equalAnswer;
    }
    /***
     * @param OW_Event $event
     */
    public function findModeratorForUser( OW_Event $event )
    {
        if (!OW::getUser()->isAuthenticated()) {
            return;
        }
        $params = $event->getParams();
        $userId = null;
        if (isset($params['userId'])) {
            $userId = $params['userId'];
        }

        if (isset($params['params']) && isset($params['params']['userId'])) {
            $userId = $params['params']['userId'];
        }

        if (empty($userId)) {
            return;
        }

        $moderatorIds = $event->getData();

        $allQuestionRoles = $this->questionRolesDao->findAll();
        if (empty($allQuestionRoles)) {
            return;
        }

        $userQuestionsValues = BOL_UserService::getInstance()->getUserViewQuestions($userId, true);

        foreach ($allQuestionRoles as $questionRole) {
            $qData = (array)json_decode($questionRole->data);
            $usersWithRole = BOL_AuthorizationUserRoleDao::getInstance()->findUsersByRoleId($questionRole->roleId);

            foreach ($usersWithRole as $userWithRole) {
                $possibleModeratorId = $userWithRole->userId;
                if (in_array($possibleModeratorId, $moderatorIds)) {
                    continue;
                }
                $adminQuestionsValues = BOL_UserService::getInstance()->getUserViewQuestions($possibleModeratorId, true);

                $canManageUsers = true;
                foreach ($qData as $key => $definedValue) {
                    if($definedValue == 'equal'){
                        if(empty($adminQuestionsValues['data'][$possibleModeratorId][$key])){
                            $canManageUsers = false;
                            break;
                        }
                        $definedValue = $adminQuestionsValues['data'][$possibleModeratorId][$key];
                    }
                    if (isset($_POST[$key])) {
                        $value = $_POST[$key];
                        $canManageUsers = $this->checkEqualAnswer($definedValue,$value);
                    } else if (isset($userQuestionsValues['data'][$userId][$key])) {
                        $value = $userQuestionsValues['data'][$userId][$key];
                        $canManageUsers = $this->checkEqualAnswer($definedValue,$value);
                    }
                }
                if ($canManageUsers) {
                    $moderatorIds[] = $possibleModeratorId;
                }
            }
        }

        $event->setData($moderatorIds);
    }

    /***
     * @param OW_Event $event
     */
    public function hasUserAuthorizeToManageUsers( OW_Event $event )
    {
        if (!OW::getUser()->isAuthenticated()) {
            return;
        }
        $params = $event->getParams();
        $userId = null;
        if (isset($params['userId'])) {
            $userId = $params['userId'];
        }

        if (isset($params['params']) && isset($params['params']['userId'])) {
            $userId = $params['params']['userId'];
        }

        if ($userId == null) {
            return;
        }

        $currentUserId = OW::getUser()->getId();
        if (isset($params['currentUserId'])) {
            $currentUserId = $params['currentUserId'];
        }
        if ($userId == $currentUserId || $userId == 0) {
            return;
        }

        $currentUserRoles = $this->getUserRoles($currentUserId);
        if (empty($currentUserRoles)) {
            return;
        }

        $questionRoles = $this->questionRolesDao->findByRoleIds($currentUserRoles);
        if (empty($questionRoles)) {
            return;
        }

        $userQuestionsValues = BOL_UserService::getInstance()->getUserViewQuestions($userId, true);
        $adminQuestionsValues = BOL_UserService::getInstance()->getUserViewQuestions($currentUserId, true);
        foreach ($questionRoles as $questionRole) {
            # one kind of moderator
            # all parts in qData should be compatible of user and moderator
            $qData = (array) json_decode($questionRole->data);

            $canManageUsers = true;
            foreach ($qData as $key => $definedValue){
                if($definedValue == 'equal'){
                    if(empty($adminQuestionsValues['data'][$currentUserId][$key])){
                        $canManageUsers = false;
                        break;
                    }
                    $definedValue = $adminQuestionsValues['data'][$currentUserId][$key];
                }
                if(isset($_POST[$key])) {
                    if ( (is_array($definedValue) && array_keys($definedValue)[0] != $_POST[$key])
                        && ($definedValue != $_POST[$key])) {
                        $canManageUsers = false;
                    }
                } else if(isset($userQuestionsValues['data'][$userId][$key])){
                    $value = $userQuestionsValues['data'][$userId][$key];
                    if (!isset($value[$definedValue])) {
                        $canManageUsers = false;
                    }
                } else if(!isset($userQuestionsValues['data'][$userId][$key])){
                    $canManageUsers = false;
                }
            }
            if ($canManageUsers) {
                $event->setData(array('valid' => true));
                return;
            }
        }

        $event->setData(array('valid' => false));
    }

    public function getUnapprovedUsersByRolesData($rolesData) {
        return $this->questionRolesDao->getUnapprovedUsersByRolesData($rolesData);
    }

    /**
     * @param $rolesData
     * @return array
     */
    public function getUsersByRolesData($rolesData) {
        return $this->questionRolesDao->getUsersByRolesData($rolesData);
    }

    /**
     * @param OW_Event $event
     */
    public function getUsersByRolesDataEvent(OW_Event $event) {
        if (!isset($event->getParams()['rolesData'])) {
            return;
        }
        $rolesData = $event->getParams()['rolesData'];
        $data = $this->getUsersByRolesData($rolesData);
        $event->setData($data);
    }

    /**
     * @param string $groupTableAlias
     * @return string
     */
    private function generateInClauseForRolesToManageSpecificUsers($groupTableAlias ='g') {
        $whereClause = " ";

        $userRoles = GROUPS_BOL_Service::getInstance()->getUserRolesToManageSpecificUsers();
        $isQuestionRoleModerator = GROUPS_BOL_Service::getInstance()->checkIfUserHasRolesToManageSpecificUsers($userRoles);

        if (!OW::getUser()->isAuthorized('groups') && !OW::getUser()->isAdmin() && $isQuestionRoleModerator) {
            $userIds = OW::getEventManager()->trigger(new OW_Event('frmquestionroles.getUsersByRolesData', array('rolesData' => $userRoles)));
            $userIds = $userIds->getData();
            if (!empty($userIds)) {
                $whereClause = ' AND `'.$groupTableAlias.'`.`userId` IN (' . OW::getDbo()->mergeInClause($userIds) . ') ';
            }
        }
        return $whereClause;
    }

    public function getUserRolesToManageSpecificUsers($userId)
    {
        if (!OW::getUser()->isAuthenticated()) {
            return null;
        }

        if ($userId == null) {
            return null;
        }

        $currentUserRoles = $this->getUserRoles($userId);
        if (empty($currentUserRoles) || sizeof($currentUserRoles) == 0) {
            return null;
        }

        $questionRoles = $this->questionRolesDao->findByRoleIds($currentUserRoles);
        if (empty($questionRoles) || sizeof($questionRoles) == 0) {
            return null;
        }

        return $questionRoles;
    }

    public function getUserRolesToManageSpecificUsersEvent(OW_Event $event) {
        $data = $event->getParams();
        if (!isset($data['userId'])) {
            return;
        }
        $userId = (int) $data['userId'];
        $event->setData($this->getUserRolesToManageSpecificUsers($userId));
    }

    /***
     * @param BASE_CLASS_EventCollector $event
     */
    public function addRoleManagementConsoleItem( BASE_CLASS_EventCollector $event )
    {
        if($this->hasAccessToRolesManagement()) {
            $event->add(array('label' => OW::getLanguage()->text('frmquestionroles', 'console_label'), 'url' => OW_Router::getInstance()->urlForRoute('frmquestionroles.index')));
        }
    }

    public function getAllSystemRoles() {
        $authService = BOL_AuthorizationService::getInstance();
        $systemRolesData = array();
        $systemRolesList = $authService->getRoleList();

        foreach ( $systemRolesList as $role )
        {
            if($role->getName() != 'guest'){
                $systemRolesData[$role->getId()] = array(
                    'dto' => $role,
                    'roleFieldId' => 'role_'.$role->getId()
                );
            }
        }

        $tplRoles = array();
        foreach ( $systemRolesList as $role )
        {
            if($role->getName() != 'guest') {
                $tplRoles[$role->sortOrder] = $role;
            }
        }

        return array(
            'roles' => $tplRoles,
            'systemRolesData' => $systemRolesData,
        );
    }

    /***
     * @param $roleId
     * @param $data
     */
    public function saveRoleWithData($roleId, $data) {
        $this->questionRolesDao->saveRoleWithData($roleId, $data);
    }

    public function onNotifyActions(BASE_CLASS_EventCollector $e)
    {
        $e->add(array(
            'section' => 'frmquestionroles',
            'action' => 'manage_question_roles',
            'sectionIcon' => 'ow_ic_files',
            'sectionLabel' => OW::getLanguage()->text('frmquestionroles', 'admin_title'),
            'description' => OW::getLanguage()->text('frmquestionroles', 'console_label'),
            'selected' => true
        ));
    }

    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frmquestionroles' => array(
                    'label' => $language->text('frmquestionroles', 'admin_title'),
                    'actions' => array(
                        'manage_question_roles' => $language->text('frmquestionroles', 'console_label'),
                    )
                )
            )
        );
    }
}
