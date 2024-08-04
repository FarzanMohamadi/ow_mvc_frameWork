<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsecurityessentials.bol
 * @since 1.0
 */
class FRMSECURITYESSENTIALS_BOL_Service
{
    const ON_AFTER_READ_URL_EMBED = 'frmsecurityessentials.on.after.read.url.embed';
    const ON_CHECK_URL_EMBED = 'frmsecurityessentials.on.check.url.embed';
    const ON_CHECK_OBJECT_BEFORE_SAVE_OR_UPDATE = 'frmsecurityessentials.on.check.object.before.save.or.update';
    const ON_BEFORE_FORM_CREATION = 'frmsecurityessentials.before.form.creation';
    const ON_AFTER_FORM_SUBMISSION = 'frmsecurityessentials.after.form.submission';
    const ON_BEFORE_HTML_STRIP = 'frmsecurityessentials.before.html.strip';
    const ON_GENERATE_REQUEST_MANAGER = 'frmsecurityessentials.on.generate.request.manager';
    const ON_CHECK_REQUEST_MANAGER = 'frmsecurityessentials.on.check.request.manager';
    const ON_CHANGE_GROUP_PRIVACY_TO_PRIVATE = 'frmsecurityessentials.on.change.group.privacy.to.private';
    const ON_RENDER_USER_PRIVACY = 'frmsecurityessentials.on.render.user.privacy';
    const CHECK_ACCESS_USERS_LIST = 'frmsecurityessentials.check.access.users.list';
    const CHECK_USER_CAN_CHANGE_ACCOUNT_TYPE = 'frmsecurityessentials.check.user.can.change.account.type';
    private static $classInstance;
    public static $PRIVACY_EVERYBODY = 'everybody';
    public static $PRIVACY_ONLY_FOR_ME = 'only_for_me';
    public static $PRIVACY_FRIENDS_ONLY = 'friends_only';

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $questionPrivacy;

    private function __construct()
    {
        $this->questionPrivacy = FRMSECURITYESSENTIALS_BOL_QuestionPrivacyDao::getInstance();
    }

    /***
     * @param $userId
     * @param $questionId
     * @return mixed
     */
    public function getQuestionPrivacy($userId, $questionId)
    {
        return $this->questionPrivacy->getQuestionPrivacy($userId, $questionId);
    }

    /***
     * @param $userId
     * @param $questionIds
     * @return mixed
     */
    public function getQuestionsPrivacy($userId, $questionIds)
    {
        return $this->questionPrivacy->getQuestionsPrivacy($userId, $questionIds);
    }

    /***
     * @param $userIds
     * @param $questionIds
     * @return mixed
     */
    public function getQuestionsPrivacyForUserList($userIds, $questionIds)
    {
        return $this->questionPrivacy->getQuestionsPrivacyForUserList($userIds, $questionIds);
    }

    /***
     * @param $questionId
     * @param $privacy
     * @return FRMSECURITYESSENTIALS_BOL_QuestionPrivacy
     */
    public function setQuestionsPrivacy($questionId, $privacy)
    {
        $userId = OW::getUser()->getId();
        $qActivity = QUESTIONS_BOL_ActivityDao::getInstance()->findActivity($questionId, 'create', $questionId);
        if (isset($qActivity)) {
            $this->checkUserOwnerId($qActivity->userId);
            return $this->questionPrivacy->setQuestionPrivacy($userId, $questionId, $privacy);
        }
        exit(json_encode(array('result' => false)));
    }

    /***
     * @param $questionId
     * @param $privacy
     * @return FRMSECURITYESSENTIALS_BOL_QuestionPrivacy
     */
    public function setProfileQuestionPrivacy($questionId, $privacy, $feedId)
    {
        $userId = OW::getUser()->getId();
        if ($userId != $feedId) {
            exit(json_encode(array('result' => false)));
        }
        return $this->questionPrivacy->setQuestionPrivacy($userId, $questionId, $privacy);
    }

    /***
     * @param $userIds
     * @param $privacy
     * @param $questionId
     * @return array
     */
    public function getQuestionsPrivacyByExceptPrivacy($userIds, $privacy, $questionId)
    {
        return $this->questionPrivacy->getQuestionsPrivacyByExceptPrivacy($userIds, $privacy, $questionId);
    }

    public function getSections($currentSection = null)
    {
        if ($currentSection == null) {
            $currentSection = 1;
        }

        $sectionsInformation = array();

        for ($i = 1; $i <= 8; $i++) {
            if ($i == 3) {
                continue;
            }
            $sections[] = array(
                'sectionId' => $i,
                'active' => $currentSection == $i ? true : false,
                'url' => OW::getRouter()->urlForRoute('frmsecurityessentials.admin.currentSection', array('currentSection' => $i)),
                'label' => $this->getPageHeaderLabel($i)
            );
        }

        $sectionsInformation['sections'] = $sections;
        $sectionsInformation['currentSection'] = $currentSection;
        return $sectionsInformation;
    }

    public function getPageHeaderLabel($sectionId)
    {
        if ($sectionId == 1) {
            return OW::getLanguage()->text('frmsecurityessentials', 'general_setting');
        } else if ($sectionId == 2) {
            return OW::getLanguage()->text('frmsecurityessentials', 'privacy_setting');
        } else if ($sectionId == 3) {
            return OW::getLanguage()->text('frmsecurityessentials', 'newsfeed_homepage_setting');
        } else if ($sectionId == 4) {
            return OW::getLanguage()->text('frmsecurityessentials', 'change_user_password_by_code');
        } else if ($sectionId == 5) {
            return OW::getLanguage()->text('frmsecurityessentials', 'set_valid_ips');
        } else if ($sectionId == 6) {
            return OW::getLanguage()->text('frmsecurityessentials', 'profile_field_privacy');
        } else if ($sectionId == 7) {
            return OW::getLanguage()->text('frmsecurityessentials', 'update_system_code');
        }else if ($sectionId == 8) {
            return OW::getLanguage()->text('frmsecurityessentials', 'public_warning_alert');
        }
    }


    public function onBeforeUsersInformationRender(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['userIdList']) && isset($params['questionList'])) {
            $questionList = $params['questionList'];
            $userIdList = $params['userIdList'];
            $notGrantUsersWithPublicSexType = array();
            $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');
            $usersWithoutPublicSexType = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->getQuestionsPrivacyByExceptPrivacy($userIdList, self::$PRIVACY_EVERYBODY, $qSex->id);
            foreach ($usersWithoutPublicSexType as $userWithoutPublicSexType) {
                $notGrantUsersWithPublicSexType[] = $userWithoutPublicSexType->userId;
            }

            $notGrantUsersWithPublicBirthdateType = array();
            $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');
            if(isset($qBdate)) {
                $usersWithoutPublicBirthdateType = FRMSECURITYESSENTIALS_BOL_Service::getInstance(
                )->getQuestionsPrivacyByExceptPrivacy($userIdList, self::$PRIVACY_EVERYBODY, $qBdate->id);
                foreach ($usersWithoutPublicBirthdateType as $userWithoutPublicBirthdateType) {
                    $notGrantUsersWithPublicBirthdateType[] = $userWithoutPublicBirthdateType->userId;
                }
            }
            $newQuestionList = array();
            foreach ($questionList as $uid => $question) {
                if (in_array($uid, $notGrantUsersWithPublicSexType)) {
                    unset($question['sex']);
                }

                if (in_array($uid, $notGrantUsersWithPublicBirthdateType)) {
                    unset($question['birthdate']);
                }

                $newQuestionList[$uid] = $question;
            }
            $event->setData(array('questionList' => $newQuestionList));
        }
    }


    public function onBeforePrivacyItemAdd(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['key'])) {
            $value = $this->getAdminDefaultValueOfPrivacy($params['key']);
            if ($value != null) {
                $event->setData(array('value' => $value));
            }
        }
    }

    public function onBeforeEmailVerifyFormRender(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['this'])) {
            if (isset($params['page']) && $params['page'] == 'verifyForm') {
                $params['this']->assign('verifyLater', '<br/><p class="ow_center"><a class="ow_lbutton" href="' . OW::getRouter()->urlForRoute('base_email_verify') . '">' . OW::getLanguage()->text('frmsecurityessentials', 'verify_using_resend_email') . '</a></p>');
            } else {
                $params['this']->assign('verifyLater', '<br/><p class="ow_center"><a class="ow_lbutton" href="' . OW::getRouter()->urlForRoute('base_email_verify_code_form') . '">' . OW::getLanguage()->text('frmsecurityessentials', 'verify_using_code') . '</a></p></br><p class="ow_center"><a class="ow_lbutton" href="' . OW::getRouter()->urlForRoute('base_sign_out') . '">' . OW::getLanguage()->text('frmsecurityessentials', 'verify_later') . '</a></p>');
            }
        }
    }

    public function onBeforeQuestionsDataProfileRender(OW_Event $event)
    {
        $params = $event->getParams();
        $ownerId = $params['userId'];
        $questions = $params['questions'];
        if (isset($params['questions']) && isset($params['userId']) && isset($params['component'])) {
            $isOwner = OW::getUser()->isAuthenticated() && $ownerId == OW::getUser()->getId();
            if (!$isOwner) {
                return;
            }
            $service = FRMSECURITYESSENTIALS_BOL_Service::getInstance();
            $questionsPrivacyButton = array();
            $questionsPrivacyIgnoreList = array();
            $actionType = 'questionsPrivacy';
            $change_privacy_label = OW::getLanguage()->text('frmsecurityessentials', 'change_privacy_label');
            $questionsId = array();
            foreach ($questions as $question) {
                $questionsId[] = $question['id'];
            }
            $questionsPrivacy = $service->getQuestionsPrivacy($ownerId, $questionsId);
            foreach ($questions as $question) {
                $privacy = null;
                if (isset($questionsPrivacy[$question['id']])) {
                    $questionPrivacy = $questionsPrivacy[$question['id']];
                    if ($questionPrivacy != null) {
                        $privacy = $questionPrivacy->privacy;
                    }
                }
                if ($privacy == null) {
                    $fieldValue = OW::getConfig()->getValue('frmsecurityessentials', 'privacy_profile_field_'.$question['name']);
                    if (isset($fieldValue) && $fieldValue != null) {
                        $privacy = $fieldValue;
                    } else {
                        $privacy = self::$PRIVACY_FRIENDS_ONLY;
                    }
                }

                $privacyButton = array('label' => $this->getPrivacyLabelByFeedId($privacy, $ownerId),
                    'imgSrc' => OW::getPluginManager()->getPlugin('frmsecurityessentials')->getStaticUrl() . 'images/' . $privacy . '.png');
                if ($ownerId == OW::getUser()->getId()) {
                    $privacyButton['onClick'] = 'javascript:showAjaxFloatBoxForChangePrivacy(\'' . $question['id'] . '\', \'' . $change_privacy_label . '\',\'' . $actionType . '\',\'' . $ownerId . '\')';
                    $privacyButton['id'] = 'sec-' . $question['id'] . '-' . $ownerId;
                }

                if (!$this->checkPrivacyOfObject($privacy, $ownerId, null, false, 'profile_user')) {
                    $questionsPrivacyIgnoreList[$question['id']] = false;
                } else if (OW::getUser()->isAuthenticated() && $ownerId == OW::getUser()->getId()) {
                    $questionsPrivacyButton[$question['id']] = $privacyButton;
                }
            }
            if(sizeof($questionsPrivacyIgnoreList) == sizeof($questions))
            {
                $params['component']->assign('hideSection', true);
            }
            $params['component']->assign('questionsPrivacyIgnoreList', $questionsPrivacyIgnoreList);
            $params['component']->assign('questionsPrivacyButton', $questionsPrivacyButton);
            $params['component']->assign('isOwner', $isOwner);

        }
    }


    public function onBeforeAlbumCreateForStatusUpdate(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['albumName'])) {
            $count = 0;
            while ($count < 20) {
                $randomName = $params['albumName'] . ' ' . rand(0, 9999999999);
                $albumName = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumByName($randomName, OW::getUser()->getId());
                if ($albumName == null) {
                    $event->setData(array('albumName' => $randomName));
                    break;
                }
                $count++;
            }
        }
    }

    public function onAfterLastPhotoRemoved(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['photoIdList']) && isset($params['fromAlbumLastPhoto'])) {
            if (in_array($params['fromAlbumLastPhoto']->id, $params['photoIdList'])) {
                $fromAlbumLastPhoto = PHOTO_BOL_PhotoDao::getInstance()->getLastPhoto($params['fromAlbumLastPhoto']->albumId, $params['photoIdList']);
                $event->setData(array('fromAlbumLastPhoto' => $fromAlbumLastPhoto));
            }
        }
    }

    public function onBeforePhotoInit(OW_Event $event)
    {
        $params = $event->getParams();
        $error = false;
        if (isset($params['username']) && isset($params['action']) && $params['action'] == 'userPhotos') {
            $user = BOL_UserService::getInstance()->findByUsername($params['username']);
            if ($user != null) {
                $eventParams = array(
                    'action' => 'photo_view_album',
                    'ownerId' => $user->getId()
                );
                $privacy = OW::getEventManager()->getInstance()->call('plugin.privacy.get_privacy', $eventParams);
                if (!OW::getUser()->isAuthenticated() && $privacy != self::$PRIVACY_EVERYBODY) {
                    $this->throwPrivacyExecption($user->getUsername(), $user->getId(), $privacy);
                }
            }
        } else if (isset($params['photoId']) && isset($params['ownerId'])) {
            $user = BOL_UserService::getInstance()->findUserById($params['ownerId']);
            $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($params['photoId']);
            if ($user != null && $photo != null) {
                $eventParams = array(
                    'action' => 'photo_view_album',
                    'ownerId' => $user->getId()
                );
                $photoPrivacy = $photo->privacy;
                $modulePrivacy = OW::getEventManager()->getInstance()->call('plugin.privacy.get_privacy', $eventParams);
                if (!OW::getUser()->isAuthenticated() && ($modulePrivacy != self::$PRIVACY_EVERYBODY || $photoPrivacy != self::$PRIVACY_EVERYBODY)) {
                    $error = true;
                } else if (OW::getUser()->isAuthenticated() && ($modulePrivacy == self::$PRIVACY_FRIENDS_ONLY || $photoPrivacy == self::$PRIVACY_FRIENDS_ONLY)) {
                    $userFriendsId = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => $user->getId()));
                    if (false !== array_search(OW::getUser()->getId(), $userFriendsId)) {
                        $error = false;
                    } else if (!OW::getUser()->isAuthenticated() || OW::getUser()->getId() != $user->getId()) {
                        $error = true;
                    }
                } else if (OW::getUser()->isAuthenticated() && ($modulePrivacy == self::$PRIVACY_ONLY_FOR_ME || $photoPrivacy == self::$PRIVACY_ONLY_FOR_ME) && OW::getUser()->getId() != $user->getId()) {
                    $error = true;
                }

                if ($error) {
                    $this->throwPrivacyExecption($user->getUsername(), $user->getId(), $modulePrivacy);
                }
            }
        } else if (isset($params['albumId']) && isset($params['action']) && $params['action'] == 'check_album_privacy') {
            $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($params['albumId']);
            $owner = BOL_UserService::getInstance()->findUserById($album->userId);
            if ($owner != null && $album != null) {
                $eventParams = array(
                    'action' => 'photo_view_album',
                    'ownerId' => $owner->getId()
                );
                $photoPrivacy = $this->getPrivacyOfAlbum($album->getId());
                $modulePrivacy = OW::getEventManager()->getInstance()->call('plugin.privacy.get_privacy', $eventParams);
                if(OW::getUser()->isAuthorized('photo'))
                {
                    $error=false;
                }
                else if (!OW::getUser()->isAuthenticated() && ($modulePrivacy != self::$PRIVACY_EVERYBODY || $photoPrivacy != self::$PRIVACY_EVERYBODY)) {
                    $error = true;
                } else if (OW::getUser()->isAuthenticated() && ($modulePrivacy == self::$PRIVACY_ONLY_FOR_ME || $photoPrivacy == self::$PRIVACY_ONLY_FOR_ME) && OW::getUser()->getId() != $owner->getId()) {
                    $error = true;
                } else if (OW::getUser()->isAuthenticated() && ($modulePrivacy == self::$PRIVACY_FRIENDS_ONLY || $photoPrivacy == self::$PRIVACY_FRIENDS_ONLY)) {
                    $userFriendsId = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => $owner->getId()));
                    if (false !== array_search(OW::getUser()->getId(), $userFriendsId)) {
                        $error = false;
                    } else if (!OW::getUser()->isAuthenticated() || OW::getUser()->getId() != $album->userId) {
                        $error = true;
                    }
                }

                if ($error) {
                    $this->throwPrivacyExecption($owner->getUsername(), $owner->getId(), $modulePrivacy);
                }
            }
        }
    }

    public function throwPrivacyExecption($username, $userId, $privacy)
    {
        $exception = new RedirectException(OW::getRouter()->urlForRoute('privacy_no_permission', array('username' => $username)));
        $langParams = array(
            'username' => $username,
            'display_name' => BOL_UserService::getInstance()->getDisplayName($userId)
        );
        $error['message'] = OW::getLanguage()->getInstance()->text('privacy', 'privacy_no_permission_message', $langParams);
        $error['privacy'] = $privacy;
        OW::getSession()->set('privacyRedirectExceptionMessage', $error['message']);
        $exception->setData($error);
        throw $exception;
    }

    public function eventAfterPhotoMove(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['toAlbum']) && isset($params['fromAlbum']) && isset($params['photoIdList'])) {
            $privacyOfToAlbum = $this->getPrivacyOfAlbum($params['toAlbum'], $params['photoIdList']);
            $privacyOfFromAlbum = $this->getPrivacyOfAlbum($params['fromAlbum']);
            foreach ($params['photoIdList'] as $photoId) {
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                if ($privacyOfToAlbum == null) {
                    if (isset($_REQUEST['statusPrivacy'])) {
                        $privacyOfToAlbum = $this->validatePrivacy($_REQUEST['statusPrivacy']);
                    } else {
                        $privacyOfToAlbum = $photo->privacy;
                    }
                }
                $this->updatePrivacyOfPhoto($photo->id, $privacyOfToAlbum);
            }

            $actionIds = $this->findActionOfDependenciesPhoto($params['toAlbum']);
            $this->updateNewsFeedActivitiesByActionIds($actionIds, $privacyOfToAlbum);

            if ($privacyOfFromAlbum != null) {
                $actionIds = $this->findActionOfDependenciesPhoto($params['fromAlbum']);
                $this->updateNewsFeedActivitiesByActionIds($actionIds, $privacyOfFromAlbum);
            }
        }
    }

    public function findActionOfDependenciesPhoto($albumId)
    {
        $actionIds = array();

        $count = PHOTO_BOL_PhotoService::getInstance()->countAlbumPhotos($albumId, array());
        $photosOfAlbum = PHOTO_BOL_PhotoService::getInstance()->findPhotoListByAlbumId($albumId, 1, $count);
        foreach ($photosOfAlbum as $photoItem) {
            $action = NEWSFEED_BOL_Service::getInstance()->findAction('multiple_photo_upload', $photoItem['uploadKey']);
            if ($action != null) {
                $actionIds[] = $action->id;
            }

            $action = NEWSFEED_BOL_Service::getInstance()->findAction('multiple_photo_upload', $photoItem['id']);
            if ($action != null) {
                $actionIds[] = $action->id;
            }

            $action = NEWSFEED_BOL_Service::getInstance()->findAction('photo_comments', $photoItem['uploadKey']);
            if ($action != null) {
                $actionIds[] = $action->id;
            }

            $action = NEWSFEED_BOL_Service::getInstance()->findAction('photo_comments', $photoItem['id']);
            if ($action != null) {
                $actionIds[] = $action->id;
                return $actionIds;
            }

        }
        return $actionIds;
    }

    public function check_permission(BASE_CLASS_EventCollector $event)
    {
        $params = $event->getParams();
        if (isset($params['action']) && $params['action'] == 'view_my_feed') {
            $privacies = array(self::$PRIVACY_EVERYBODY, self::$PRIVACY_FRIENDS_ONLY, self::$PRIVACY_ONLY_FOR_ME, null);
            foreach ($privacies as $privacy) {
                $data = array($privacy => array('blocked' => false));
                $event->add($data);
            }

        }
    }

    public function onBeforeFeedActivity(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['activityType'])) {
            $activityType = $params['activityType'];
            if (in_array($activityType, array('like', 'comment'))) {
                $event->setData(array('createFeed' => false));
            } else {
                if (isset($params['actionId'])) {
                    $action = null;
                    if (isset($params['action'])) {
                        $action = $params['action'];
                    }
                    if ($action == null) {
                        $action = NEWSFEED_BOL_Service::getInstance()->findActionById($params['actionId']);
                    }
                    if ($action != null && $action->entityType == 'friend_add') {
                        $event->setData(array('createFeed' => false));
                    }
                }
            }
        }

    }

    public function getActionPrivacy(OW_Event $event)
    {
        $params = $event->getParams();

        if (isset($params['ownerId']) && isset($params['action']) && isset($_REQUEST['statusPrivacy']) && ($params['action'] == 'photo_view_album' || $params['action'] == 'video_view_video')) {
            if (isset($_REQUEST['album-name']) && isset($_REQUEST['album']) && $_REQUEST['album-name'] == $_REQUEST['album']) {
                $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumByName($_REQUEST['album-name'], $params['ownerId']);
                $privacy = $this->getPrivacyOfAlbum($album->id);
                if ($privacy != null) {
                    $event->setData(array('privacy' => $privacy));
                } else {
                    $event->setData(array('privacy' => $this->validatePrivacy($_REQUEST['statusPrivacy'])));
                }
            } else {
                $event->setData(array('privacy' => $this->validatePrivacy($_REQUEST['statusPrivacy'])));
            }
        }
    }

    public function onBeforeVideoUploadFormRenderer(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['form'])) {
            $form = $params['form'];
            $form->addElement($this->createStatusPrivacyElement('video_default_privacy', $params));
        }
    }

    public function onBeforeVideoUploadComponentRenderer(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['form']) && isset($params['component'])) {
            $form = $params['form'];
            if ($form->getElement('statusPrivacy') != null) {
                $params['component']->assign('statusPrivacyField', true);
            }
        }
    }

    public function getActionValueOfPrivacy($privacyKey, $userId)
    {
        if (OW::getUser()->isAuthenticated() && class_exists('PRIVACY_BOL_ActionService')) {
            $userPrivacy = PRIVACY_BOL_ActionService::getInstance()->getActionValue($privacyKey, $userId);
            if ($userPrivacy != null) {
                return $userPrivacy;
            }
        }
        $adminValue = OW::getConfig()->getValue('frmsecurityessentials', $privacyKey);
        if ($adminValue != null) {
            return $adminValue;
        }
        return self::$PRIVACY_FRIENDS_ONLY;
    }


    public function onBeforePhotoUploadFormRenderer(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['form'])) {
            $form = $params['form'];
            $form->addElement($this->createStatusPrivacyElement('photo_default_privacy', $params));
            if (isset($params['this'])) {
                $params['this']->assign('statusPrivacy', true);
            }
        }
    }

    public function onBeforeCreateFormUsingFieldPrivacy(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['privacyKey'])) {
            $event->setData(array('privacyElement' => $this->createStatusPrivacyElement($params['privacyKey'])));
        }
    }

    public function onBeforeContentListQueryExecute(OW_Event $event)
    {
        $params = $event->getParams();
        $privacyKey = '';
        $pluginKey = '';
        $whereCondition = '';
        if (isset($params['entityType']) || isset($params['objectTableName'])) {
            if ((isset($params['entityType']) && ($params['entityType'] == 'video_rates' || $params['entityType'] == 'video')) || (isset($params['objectType']) && $params['objectType'] == 'video')) {
                $privacyKey = 'video_view_video';
                $pluginKey = 'video';
            } else if ((isset($params['entityType']) && ($params['entityType'] == 'photo_rates' || $params['entityType'] == 'photo_comments')) || (isset($params['objectType']) && $params['objectType'] == 'photo')) {
                $privacyKey = 'photo_view_album';
                $pluginKey = 'photo';
            } else if (isset($params['objectType']) && $params['objectType'] == 'question') {
                $privacyKey = 'view_my_questions';
                $pluginKey = 'questions';
            }

            if (isset($params['objectTableName']) && class_exists('PRIVACY_BOL_ActionDataDao') && isset($params['listType']) && in_array($params['listType'], array('latest', 'featured'))) {
                if (!isset($params['privacyTableNameExist']) || $params['privacyTableNameExist']) {
                    if (isset($params['privacyTableName'])) {
                        $justFriends = false;
                        if (isset($params['just_friends']) && $params['just_friends']) {
                            $justFriends = true;
                        }
                        $whereCondition = $this->buildUserPrivacyConditionQuery($params['objectTableName'], $params['privacyTableName'], $privacyKey, $justFriends);
                    } else {
                        $whereCondition = $this->buildUserPrivacyConditionQuery($params['objectTableName'], $params['objectTableName'], $privacyKey);
                    }
                } else if (isset($params['privacyTableNameExist']) && !$params['privacyTableNameExist'] && $params['object_list'] == 'album') {
                    if(isset($params["albumOwnerId"]))
                        $whereCondition = $this->buildUserAlbumPrivacyConditionQuery($params['objectTableName'], $privacyKey, $params["albumOwnerId"]);
                    else
                        $whereCondition = $this->buildUserAlbumPrivacyConditionQuery($params['objectTableName'], $privacyKey);
                }
                $event->setData(array('where' => $whereCondition, 'params' => array('pluginKey' => $pluginKey, 'privacyKey' => $privacyKey)));
            } else if (isset($params['commentEntityTableName']) &&
                class_exists('BOL_CommentEntityDao') &&
                class_exists('PRIVACY_BOL_ActionDataDao') &&
                $params['entityType'] == 'photo_comments' &&
                isset($params['listType']) &&
                in_array($params['listType'], array('commentDao'))) {
                //put privacy condition in most discussed photo
                $whereCondition = $this->buildQueryForPhotoWithEntityIdPrivacyCondition($params['commentEntityTableName']);
                $privacyCondition = $this->buildUserPrivacyConditionQuery('album', 'pho', $privacyKey);
                $whereCondition .= $privacyCondition;
                $whereCondition .= ') >0';
                $event->setData(array('where' => $whereCondition, 'params' => array('pluginKey' => $pluginKey, 'privacyKey' => $privacyKey)));
            } else if (isset($params['tagEntityTableName']) && $params['entityType'] == 'video' && class_exists('PRIVACY_BOL_ActionDataDao')) {
                //put privacy condition in video tag search
                $whereCondition = $this->buildQueryForVideoWithEntityIdPrivacyCondition($params['tagEntityTableName']);
                $whereCondition .= $this->buildUserPrivacyConditionQuery('video', 'video', $privacyKey);
                $whereCondition .= ') >0';
                $event->setData(array('where' => $whereCondition, 'params' => array('pluginKey' => $pluginKey, 'privacyKey' => $privacyKey)));
            } else if (isset($params['rateTableName']) && class_exists('BOL_RateDao') && class_exists('PRIVACY_BOL_ActionDataDao') && isset($params['listType']) && in_array($params['listType'], array('rateDao'))) {
                if ($params['entityType'] == 'photo_rates') {
                    //put privacy condition in top rated photo
                    $whereCondition = $this->buildQueryForPhotoWithEntityIdPrivacyCondition($params['rateTableName']);
                    $whereCondition .= $this->buildUserPrivacyConditionQuery('album', 'pho', $privacyKey);
                    $whereCondition .= ') >0';
                } else if ($params['entityType'] == 'video_rates') {
                    //put privacy condition in top rated video
                    $whereCondition = $this->buildQueryForVideoWithEntityIdPrivacyCondition($params['rateTableName']);
                    $whereCondition .= $this->buildUserPrivacyConditionQuery('video', 'video', $privacyKey);
                    $whereCondition .= ') >0';
                }
                if ($whereCondition != '') {
                    $event->setData(array('where' => $whereCondition, 'params' => array('pluginKey' => $pluginKey, 'privacyKey' => $privacyKey)));
                }
            }
        } else if (isset($params['example']) && isset($params['ownerId']) && isset($params['objectType']) && $params['objectType'] == 'video') {
            $example = $params['example'];
            $ownerId = $params['ownerId'];
            if (!OW::getUser()->isAuthenticated()) {
                $example->andFieldInArray('privacy', array('everybody'));
            } else if (OW::getUser()->getId() != $ownerId) {
                $userFriendsId = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => $ownerId));
                if (false !== array_search(OW::getUser()->getId(), $userFriendsId)) {
                    $example->andFieldInArray('privacy', array('everybody', 'friends_only'));
                } else {
                    $example->andFieldInArray('privacy', array('everybody'));
                }
            }
            $event->setData(array('example' => $example));
        } else {
            return;
        }
    }

    /***
     * build query for privacy condition of comment and tag photo list
     * @param $tableName
     * @return string
     */
    public function buildQueryForPhotoWithEntityIdPrivacyCondition($tableName)
    {
        $whereCondition = ' and (select count(*) from ' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . ' as album, ' . PHOTO_BOL_PhotoDao::getInstance()->getTableName() . ' as pho where album.id = pho.albumId and pho.id = ' . $tableName . '.`entityId`';
        return $whereCondition;
    }

    /***
     * build query for privacy condition of comment and tag video list
     * @param $tableName
     * @return string
     */
    public function buildQueryForVideoWithEntityIdPrivacyCondition($tableName)
    {
        $whereCondition = ' and (select count(*) from ' . VIDEO_BOL_ClipDao::getInstance()->getTableName() . ' as video where video.id = ' . $tableName . '.`entityId`';
        return $whereCondition;
    }

    /***
     * build privacy condition query for fetching content list as video and photo
     * @param null $objectTableName
     * @param null $privacyOfObjectTableName
     * @param null $privacyKey
     * @param bool $justFriends
     * @return string
     */
    public function buildUserPrivacyConditionQuery($objectTableName = null, $privacyOfObjectTableName = null, $privacyKey = null, $justFriends = false)
    {
        if ($objectTableName == null) {
            return "";
        }
        $adminPrivacy = "false";
        $config = OW::getConfig();
        if ($privacyKey != null && $privacyKey != '' && $config->configExists('frmsecurityessentials', $privacyKey) && $config->getValue('frmsecurityessentials', $privacyKey) == self::$PRIVACY_EVERYBODY) {
            $adminPrivacy = "true";
        }
        $queryForPublicContent = $privacyOfObjectTableName . '.`privacy` = \'' . self::$PRIVACY_EVERYBODY . '\' and ( ' . $objectTableName . '.`userId` in (select pad.userId from ' . PRIVACY_BOL_ActionDataDao::getInstance()->getTableName() . ' AS pad where pad.key = :privacyKey and pad.pluginKey = :pluginKey and  value = \'' . self::$PRIVACY_EVERYBODY . '\'  ) or (' . $this->getAdminPrivacyForPrivacyDataQueryCondition($adminPrivacy, $objectTableName) . ') ) ';
        $whereCondition = "";
        if (!$justFriends) {
            $whereCondition = ' and ( (' . $queryForPublicContent . ') ';
        } else {
            $whereCondition = ' and ( 0 ';
        }

        if (OW::getUser()->isAuthenticated()) {
            $currentUserId = OW::getUser()->getId();

            $queryForOwner = $objectTableName . '.`userId` = ' . $currentUserId;
            $queryForFriends = '';
            if (class_exists('FRIENDS_BOL_FriendshipDao')) {
                $adminPrivacy = "false";
                if ($privacyKey != null && $privacyKey != '' && $config->configExists('frmsecurityessentials', $privacyKey) && $config->getValue('frmsecurityessentials', $privacyKey) != self::$PRIVACY_ONLY_FOR_ME) {
                    $adminPrivacy = "true";
                }
                $queryForFriends = $privacyOfObjectTableName . '.`privacy` != \'' . self::$PRIVACY_ONLY_FOR_ME . '\' and ' . $objectTableName . '.`userId` in (SELECT ff.`userId` FROM ' . FRIENDS_BOL_FriendshipDao::getInstance()->getTableName() . ' AS ff WHERE ff.friendId = ' . $currentUserId . ' AND ff.`status` = \'active\' union SELECT ff.`friendId` as userId FROM ' . FRIENDS_BOL_FriendshipDao::getInstance()->getTableName() . ' AS ff WHERE ff.userId = ' . $currentUserId . ' AND ff.`status` = \'active\') and ( ' . $objectTableName . '.`userId` in (select pad.userId from ' . PRIVACY_BOL_ActionDataDao::getInstance()->getTableName() . ' AS pad where pad.key = :privacyKey and pad.pluginKey = :pluginKey and  value in (\'' . self::$PRIVACY_EVERYBODY . '\', \'' . self::$PRIVACY_FRIENDS_ONLY . '\')) or (' . $this->getAdminPrivacyForPrivacyDataQueryCondition($adminPrivacy, $objectTableName) . ')  )';
            }
            if (!$justFriends) {

                $whereCondition .= ' or (' . $queryForOwner . ')';
            }
            if($queryForFriends != ''){
                $whereCondition .= ' or (' . $queryForFriends . ')';
            }

        }

        $whereCondition .= ')';

        if (OW::getUser()->isAdmin()) {
            $whereCondition = ' or ( 1<0 ' . $whereCondition . ' )';
        }
        return $whereCondition;
    }

    public function getAdminPrivacyForPrivacyDataQueryCondition($adminPrivacy, $objectTableName)
    {
        return ' ' . $adminPrivacy . ' and ' . $objectTableName . '.`userId` not in (select pad.userId from ' . PRIVACY_BOL_ActionDataDao::getInstance()->getTableName() . ' AS pad where pad.key = :privacyKey and pad.pluginKey = :pluginKey) ';
    }

    /***
     * build privacy condition query for fetching content list as photo album list
     * @param null $objectTableName
     * @param null $privacyKey
     * @return string
     */
    public function buildUserAlbumPrivacyConditionQuery($objectTableName = null, $privacyKey = null,$albumOwnerId = null)
    {
        if ($objectTableName == null) {
            return "";
        }
        $adminPrivacy = "false";
        $config = OW::getConfig();
        if ($privacyKey != null && $privacyKey != '' && $config->configExists('frmsecurityessentials', $privacyKey) && $config->getValue('frmsecurityessentials', $privacyKey) == self::$PRIVACY_EVERYBODY) {
            $adminPrivacy = "true";
        }
        $queryForPublicContent = '(select count(*) from ' . OW_DB_PREFIX . 'photo as pho where pho.albumId = ' . $objectTableName . '.`id` and privacy = \'' . self::$PRIVACY_EVERYBODY . '\' >0 ) and ( ' . $objectTableName . '.`userId` in (select pad.userId from ' . PRIVACY_BOL_ActionDataDao::getInstance()->getTableName() . ' AS pad where pad.key = :privacyKey and pad.pluginKey = :pluginKey and  value = \'' . self::$PRIVACY_EVERYBODY . '\' ) or ' . $adminPrivacy . ' ) ';
        $whereCondition = ' and ( (' . $queryForPublicContent . ') ';
        if (OW::getUser()->isAuthenticated()) {
            $currentUserId = OW::getUser()->getId();
            if(OW::getUser()->isAdmin() && $privacyKey == 'photo_view_album' && $albumOwnerId != null)
                $currentUserId = $albumOwnerId;

            $queryForOwner = $objectTableName . '.`userId` = ' . $currentUserId;
            $queryForFriends = '';
            if (class_exists('FRIENDS_BOL_FriendshipDao')) {
                $adminPrivacy = "false";
                if ($privacyKey != null && $privacyKey != '' && $config->configExists('frmsecurityessentials', $privacyKey) && $config->getValue('frmsecurityessentials', $privacyKey) != self::$PRIVACY_ONLY_FOR_ME) {
                    $adminPrivacy = "true";
                }
                $queryForFriends = '(select count(*) from ' . OW_DB_PREFIX . 'photo as pho where pho.albumId = ' . $objectTableName . '.`id` and privacy != \'' . self::$PRIVACY_EVERYBODY . '\' >0 ) and ' . $objectTableName . '.`userId` in (SELECT ff.`userId` FROM ' . FRIENDS_BOL_FriendshipDao::getInstance()->getTableName() . ' AS ff WHERE ff.friendId = ' . $currentUserId . ' AND ff.`status` = \'active\' union SELECT ff.`friendId` as userId FROM ' . FRIENDS_BOL_FriendshipDao::getInstance()->getTableName() . ' AS ff WHERE ff.userId = ' . $currentUserId . ' AND ff.`status` = \'active\') and ( ' . $objectTableName . '.`userId` in (select pad.userId from ' . PRIVACY_BOL_ActionDataDao::getInstance()->getTableName() . ' AS pad where pad.key = :privacyKey and pad.pluginKey = :pluginKey and  value in (\'' . self::$PRIVACY_EVERYBODY . '\', \'' . self::$PRIVACY_FRIENDS_ONLY . '\')) or ' . $adminPrivacy . '  )';
            }
            $whereCondition .= ' or (' . $queryForOwner . ')';
            $whereCondition .= ' or (' . $queryForFriends . ')';
        }

        $whereCondition .= ')';

        return $whereCondition;
    }

    public function getPrivacyOfAlbum($albumId, $excludeIds = array())
    {
        if (class_exists('PHOTO_BOL_PhotoDao')) {
            $photosOfAlbum = PHOTO_BOL_PhotoDao::getInstance()->getAlbumPhotos($albumId, 1, 1, $excludeIds);
            if (is_array($photosOfAlbum) && sizeof($photosOfAlbum) > 0) {
                return $photosOfAlbum[0]->privacy;
            }
        }

        return null;
    }

    public function onReadyResponseOfPhoto(OW_Event $event)
    {
        $data = $event->getData();
        if (isset($data['data']['photoList'])) {
            $change_privacy_label = OW::getLanguage()->text('frmsecurityessentials', 'change_privacy_label');
            $photos = array();
            foreach ($data['data']['photoList'] as $photo) {
                $objectId = $photo['id'];
                $feedId = $photo['userId'];
                $privacy = null;
                if (isset($photo['privacy'])) {
                    $privacy = $photo['privacy'];
                    $actionType = 'photo_comments';
                } else if (!isset($photo['albumId']) && isset($photo['albumUrl'])) {
                    $albumPrivacy = $this->getPrivacyOfAlbum($photo['id']);
                    if ($albumPrivacy != null) {
                        $privacy = $albumPrivacy;
                        $actionType = 'album';
                    }
                }
                $privacyButton = array('label' => $this->getPrivacyLabelByFeedId($privacy, $feedId),
                    'imgSrc' => OW::getPluginManager()->getPlugin('frmsecurityessentials')->getStaticUrl() . 'images/' . $privacy . '.svg');
                if ($feedId == OW::getUser()->getId()) {
                    $privacyButton['onClick'] = 'javascript:showAjaxFloatBoxForChangePrivacy(\'' . $objectId . '\', \'' . $change_privacy_label . '\',\'' . $actionType . '\',\'' . $feedId . '\')';
                    $privacyButton['id'] = 'sec-' . $objectId . '-' . $feedId;
                }
                $photo['privacy_label'] = $privacyButton;
                $photos[] = $photo;
            }
            $data['data']['photoList'] = $photos;
            $event->setData($data);
        }
    }

    public function createStatusPrivacyElement($privacyKey, $params = null)
    {
        $statusPrivacy = new Selectbox('statusPrivacy');
        $statusPrivacy->setLabel(OW::getLanguage()->text('frmsecurityessentials', 'change_privacy_label'));
        $options = array();
        $options[self::$PRIVACY_EVERYBODY] = OW::getLanguage()->text("privacy", "privacy_everybody");
        $options[self::$PRIVACY_ONLY_FOR_ME] = OW::getLanguage()->text("privacy", "privacy_only_for_me");
        $options[self::$PRIVACY_FRIENDS_ONLY] = OW::getLanguage()->text("friends", "privacy_friends_only");
        $statusPrivacy->setHasInvitation(false);
        $statusPrivacy->setOptions($options);
        $statusPrivacy->addAttribute('class', 'statusPrivacy');
        $statusPrivacy->setRequired();
        $defaultPrivacy = $this->getActionValueOfPrivacy($privacyKey, OW::getUser()->getId());
        if (isset($params['albumId'])) {
            $albumPrivacy = $this->getPrivacyOfAlbum($params['albumId']);
            if ($albumPrivacy != null) {
                $defaultPrivacy = $albumPrivacy;
            }
        }
        if (isset($params['clipId'])) {
            $videoPrivacy = $this->getPrivacyOfVideo($params['clipId']);
            if ($videoPrivacy != null) {
                $defaultPrivacy = $videoPrivacy;
            }
        }
        if ($params != null && array_key_exists('albumId', $params)) {
            $statusPrivacy->setLabel(OW::getLanguage()->text('frmsecurityessentials', 'change_privacy_of_album_label'));
        }
        if (isset($params['data']) && isset($params['data']['statusPrivacy'])) {
            $defaultPrivacy = $params['data']['statusPrivacy'];
        }
        $statusPrivacy->setValue($defaultPrivacy);
        return $statusPrivacy;
    }

    public function getPrivacyOfVideo($clipId)
    {

        if (class_exists('VIDEO_BOL_ClipService')) {
            $clip = VIDEO_BOL_ClipService::getInstance()->findClipById($clipId);
            if ($clip != null) {
                return $clip->privacy;
            }
        }

        return null;
    }

    public function privacyOnChangeActionPrivacy(OW_Event $event)
    {
        $params = $event->getParams();
        $userId = $params['userId'];
        $actionList = $params['actionList'];
        if (isset($actionList) && isset($userId) && isset($actionList['last_post_of_others_newsfeed'])) {
            $privacy = $actionList['last_post_of_others_newsfeed'];
            $getActivityQuery = 'select a.id from ' . OW_DB_PREFIX . 'newsfeed_activity a, ' . OW_DB_PREFIX . 'newsfeed_action_feed ff where a.id = ff.activityId and ff.feedId = ' . $userId . ' and a.userId!=' . $userId;
            $activityIds = OW::getDbo()->queryForList($getActivityQuery);
            $activityIdsImplodes = array();
            foreach ($activityIds as $activityId) {
                $activityIdsImplodes[] = $activityId['id'];
            }
            if (count($activityIdsImplodes) > 0) { //issa added. don't remove
                $updateQuery = 'update ' . OW_DB_PREFIX . 'newsfeed_activity activity set activity.privacy = \'' . $privacy . '\' where activity.id in(' . implode(",", $activityIdsImplodes) . ')';
                OW::getDbo()->query($updateQuery);
            }
        }

        if (isset($actionList) && isset($userId) && isset($actionList['last_post_of_myself_newsfeed'])) {
            $privacy = $actionList['last_post_of_myself_newsfeed'];
            $getActivityQuery = 'select a.id from ' . OW_DB_PREFIX . 'newsfeed_activity a, ' . OW_DB_PREFIX . 'newsfeed_action_feed ff where a.id = ff.activityId and ff.feedId = ' . $userId . ' and a.userId=' . $userId;
            $activityIds = OW::getDbo()->queryForList($getActivityQuery);
            $activityIdsImplodes = array();
            foreach ($activityIds as $activityId) {
                $activityIdsImplodes[] = $activityId['id'];
            }
            if (count($activityIdsImplodes) > 0) { //issa added. don't remove
                $updateQuery = 'update ' . OW_DB_PREFIX . 'newsfeed_activity activity set activity.privacy = \'' . $privacy . '\' where activity.id in(' . implode(",", $activityIdsImplodes) . ')';
                OW::getDbo()->query($updateQuery);
            }
        }
    }

    public function onQueryFeedCreate(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['feedType']) && $params['feedType'] == 'groups') {
            $event->setData(array('privacy' => '\'' . self::$PRIVACY_EVERYBODY . '\',\'' . self::$PRIVACY_FRIENDS_ONLY . '\',\'' . self::$PRIVACY_ONLY_FOR_ME . '\''));
        } else if (isset($params['feedId'])) {
            $feedId = $params['feedId'];
            if ($feedId == OW::getUser()->getId()) {
                $event->setData(array('privacy' => '\'' . self::$PRIVACY_EVERYBODY . '\',\'' . self::$PRIVACY_FRIENDS_ONLY . '\',\'' . self::$PRIVACY_ONLY_FOR_ME . '\''));
            } else {
                $ownerFriendsId = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => $feedId));
                if (!in_array(OW::getUser()->getId(), $ownerFriendsId)) {
                    $event->setData(array('privacy' => '\'' . self::$PRIVACY_EVERYBODY . '\''));
                } else {
                    $event->setData(array('privacy' => '\'' . self::$PRIVACY_EVERYBODY . '\',\'' . self::$PRIVACY_FRIENDS_ONLY . '\''));
                }
            }
        }

    }

    public function onBeforeUpdateStatusFormCreateInProfile(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['userId'])) {
            $userId = $params['userId'];
            if ($userId != OW::getUser()->getId()) {
                $whoCanPostPrivacy = $this->getActionValueOfPrivacy('who_post_on_newsfeed', $userId);
                if ($whoCanPostPrivacy == self::$PRIVACY_FRIENDS_ONLY) {
                    $ownerFriendsId = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => $userId));
                    if (isset($ownerFriendsId) && !in_array(OW::getUser()->getId(), $ownerFriendsId)) {
                        $event->setData(array('showUpdateStatusForm' => false));
                    }
                } else if ($whoCanPostPrivacy == self::$PRIVACY_ONLY_FOR_ME) {
                    $event->setData(array('showUpdateStatusForm' => false));
                }
            }
        }
    }

    public function onBeforeUpdateStatusFormCreate(OW_Event $event)
    {
        //Descide to show update status form in public page (false=hide)
        $event->setData(array('showUpdateStatusForm' => false));
    }

    public function privacyAddAction(BASE_CLASS_EventCollector $event)
    {
        $language = OW::getLanguage();

        $actions = array('my_post_on_feed_newsfeed', 'other_post_on_feed_newsfeed', 'last_post_of_others_newsfeed', 'who_post_on_newsfeed', 'video_default_privacy', 'last_post_of_myself_newsfeed');
        foreach ($actions as $action) {
            $information = $this->getInformationOfPrivacyField($action);
            $description = '';
            if (isset($information['description'])) {
                $description = $information['description'];
            }

            $defaultValue = self::$PRIVACY_FRIENDS_ONLY;
            if (isset($information['defaultValue'])) {
                $defaultValue = $information['defaultValue'];
            }

            $action = array(
                'key' => $action,
                'pluginKey' => 'frmsecurityessentials',
                'label' => $language->text('frmsecurityessentials', $action),
                'description' => $description,
                'defaultValue' => $defaultValue
            );

            $event->add($action);
        }
    }

    public function getInformationOfPrivacyField($privacyKey)
    {
        $information = array();
        if ($privacyKey == 'last_post_of_myself_newsfeed') {
            $information['description'] = OW::getLanguage()->text('frmsecurityessentials', 'last_post_of_myself_newsfeed_description');
        } else if ($privacyKey == 'last_post_of_others_newsfeed') {
            $information['description'] = OW::getLanguage()->text('frmsecurityessentials', 'last_post_of_others_newsfeed_description');
        } else if ($privacyKey == 'my_post_on_feed_newsfeed') {
            $information['description'] = OW::getLanguage()->text('frmsecurityessentials', 'my_post_on_feed_newsfeed_description');
        } else if ($privacyKey == 'other_post_on_feed_newsfeed') {
            $information['description'] = OW::getLanguage()->text('frmsecurityessentials', 'other_post_on_feed_newsfeed_description');
        } else if ($privacyKey == 'who_post_on_newsfeed') {
            $information['description'] = OW::getLanguage()->text('frmsecurityessentials', 'who_post_on_newsfeed_description');
        } else if ($privacyKey == 'video_default_privacy') {
            $information['description'] = OW::getLanguage()->text('frmsecurityessentials', 'video_default_privacy_description');
        }

        $adminDefaultValue = $this->getAdminDefaultValueOfPrivacy($privacyKey);
        if ($adminDefaultValue != null) {
            $information['defaultValue'] = $adminDefaultValue;
        }

        return $information;
    }

    public function getAdminDefaultValueOfPrivacy($privacyKey)
    {
        return OW::getConfig()->getValue('frmsecurityessentials', $privacyKey);
    }

    public function updatePrivacyOfVideo($objectId, $privacy)
    {
        $videoService = VIDEO_BOL_ClipService::getInstance();
        $video = $videoService->findClipById($objectId);
        if ($video != null) {
            $this->checkUserOwnerId($video->userId);
            $video->privacy = $privacy;
            $videoService->updateClip($video);
            return $video->userId;
        }
        return null;
    }

    public function getActionPrivacyByActionId($actionId)
    {
        $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findIdListByActionIds(array($actionId));
        foreach ($activities as $activityId) {
            $activity = NEWSFEED_BOL_Service::getInstance()->findActivity($activityId)[0];
            if ($activity->activityType == 'create') {
                return $activity->privacy;
            }
        }
        return null;
    }

    public function getActionOwner($actionId)
    {
        $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findIdListByActionIds(array($actionId));
        foreach ($activities as $activityId) {
            $activity = NEWSFEED_BOL_Service::getInstance()->findActivity($activityId)[0];
            if ($activity->activityType == 'create') {
                return $activity->userId;
            }
        }
        return null;
    }

    public function updatePrivacyOfPhoto($objectId, $privacy)
    {
        $photoService = PHOTO_BOL_PhotoService::getInstance();
        $photo = $photoService->findPhotoById($objectId);
        $photoOwner = $photoService->findPhotoOwner($photo->id);
        $this->checkUserOwnerId($photoOwner);
        $photo->privacy = $privacy;
        $photoService->updatePhoto($photo);
        return $photoOwner;
    }

    public function getPhotoOwner($objectId)
    {
        $photoService = PHOTO_BOL_PhotoService::getInstance();
        $photo = $photoService->findPhotoById($objectId);
        $photoOwner = $photoService->findPhotoOwner($photo->id);
        return $photoOwner;
    }

    public function updatePrivacyOfMultiplePhoto($photoIds, $privacy)
    {
        $photoOwner = '';
        $photoSampleId = null;
        foreach ($photoIds as $photoId) {
            $photoSampleId = $photoId;
            $photoOwner = $this->updatePrivacyOfPhoto($photoId, $privacy);
        }
        if ($photoSampleId != null) {
            $albumId = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoSampleId)->albumId;
            $this->updatePrivacyOfPhotosByAlbumId($albumId, $privacy);
        }
        return $photoOwner;
    }

    public function updatePrivacyOfPhotosByAlbumId($objectId, $privacy)
    {
        $actionId = array();
        $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($objectId);
        $count = PHOTO_BOL_PhotoService::getInstance()->countAlbumPhotos($album->id, array());
        $photosOfAlbum = PHOTO_BOL_PhotoService::getInstance()->findPhotoListByAlbumId($album->id, 1, $count);
        foreach ($photosOfAlbum as $photo) {
            $photoOwner = $this->updatePrivacyOfPhoto($photo['id'], $privacy);
            $action = NEWSFEED_BOL_Service::getInstance()->findAction('photo_comments', $photo['id']);
            if ($action != null) {
                if ($this->getActionOwner($action->id) == $photoOwner) {
                    $actionId[] = $action->id;
                }
            } else {
                $action = NEWSFEED_BOL_Service::getInstance()->findAction('multiple_photo_upload', $photo['uploadKey']);
                if ($action != null) {
                    if ($this->getActionOwner($action->id) == $photoOwner) {
                        $actionId[] = $action->id;
                    }
                }
            }
        }
        return array('userId' => $album->userId, 'actionId' => $actionId);
    }

    public function updateNewsFeedActivitiesByActionId($activities, $privacy)
    {
        $privacy = $this->validatePrivacy($privacy);

        //check user creator
        foreach ($activities as $activityId) {
            $activity = NEWSFEED_BOL_Service::getInstance()->findActivity($activityId)[0];
            if ($activity->activityType == 'create') {
                $feedIdFromActivity = null;
                $feedIdFromActivities = NEWSFEED_BOL_ActionFeedDao::getInstance()->findByActivityIds(array($activity->id));
                foreach ($feedIdFromActivities as $feedFromActivity) {
                    if ($feedFromActivity->feedType == "user") {
                        $feedIdFromActivity = $feedFromActivity->feedId;
                    }
                }
                if (empty($feedIdFromActivity) || OW::getUser()->getId() != $feedIdFromActivity) {
                    $this->checkUserOwnerId($activity->userId);
                }
            }
        }

        //change privacy of all activities from action
        foreach ($activities as $activityId) {
            $activity = NEWSFEED_BOL_Service::getInstance()->findActivity($activityId)[0];
            if ($privacy == self::$PRIVACY_ONLY_FOR_ME && $activity->activityType == 'subscribe' && OW::getUser()->isAuthenticated() && $activity->userId != OW::getUser()->getId()) {
                NEWSFEED_BOL_Service::getInstance()->removeActivity("subscribe.{$activity->userId}:$activity->actionId");
            } else {
//                $this->checkUserOwnerId($activity->userId);
                $activity->privacy = $privacy;
                NEWSFEED_BOL_Service::getInstance()->saveActivity($activity);
            }
        }
    }

    public function onBeforeUsedFeedListQueryExecuted(OW_Event $event)
    {
        $where = array();
        $where['followerPrivacyWhereCondition'] = ' and (activity.privacy != \'' . self::$PRIVACY_ONLY_FOR_ME . '\' || activity.userId=:u) ';
        $where['viewerActivityPrivacyWhereCondition'] = ' and action.id not in(select activityPrivacy.actionId from ' . OW_DB_PREFIX . 'newsfeed_activity activityPrivacy where activityPrivacy.activityType = :ac and activityPrivacy.privacy = \'' . self::$PRIVACY_ONLY_FOR_ME . '\' and activityPrivacy.userId != :u) ';
        $event->setData(array('whereConditionPrivacy' => $where));
    }

    public function onBeforeUserDisapproveAfterEditProfile(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['params'])) {
            $paramsData = $params['params'];
            if (isset($paramsData['forEditProfile']) && $paramsData['forEditProfile']) {
                $disableUserDisapprove = OW::getConfig()->getValue('frmsecurityessentials', 'approveUserAfterEditProfile');
                $event->setData(array('disapprove' => !$disableUserDisapprove));
            }
        }

        if (isset($params['checkApproveEnabled']) && $params['checkApproveEnabled']) {
            $approveEnabled = OW::getConfig()->getValue('base', 'mandatory_user_approve');
            $disableUserDisapprove = OW::getConfig()->getValue('frmsecurityessentials', 'approveUserAfterEditProfile');
            //mandatory_user_approve is first step that must be evaluated to check whether approval of system is activated or not, if this config is active then second config (approveUserAfterEditProfile) must be evaluated
            if (isset($approveEnabled) && $approveEnabled) {
                $event->setData(array('approveEnabled' => !$disableUserDisapprove));
            }
        }

    }

    public function checkUserOwnerId($ownerId, $feedId = null)
    {
        if ($feedId != null && $feedId != '' && $feedId == OW::getUser()->getId()) {
            return;
        } else if (!OW::getUser()->isAuthenticated() || OW::getUser()->getId() != $ownerId) {
            exit(json_encode(array('result' => false)));
        }
    }

    public function updateNewsFeedActivitiesByActionIds($actionIds, $privacy)
    {
        $activities = array();
        if (is_array($actionIds)) {
            $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findIdListByActionIds($actionIds);
        } else {
            $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findIdListByActionIds(array($actionIds));
        }
        $this->updateNewsFeedActivitiesByActionId($activities, $privacy);
    }

    public function onAfterActivity(OW_Event $event)
    {
        $params = $event->getParams();
        $feedId = null;
        if (isset($params['feedId'])) {
            $feedId = $params['feedId'];
        }
        $feedType = null;
        if (isset($params['feedType'])) {
            $feedType = $params['feedType'];
        }
        $entityType = null;
        if (isset($params['entityType'])) {
            $entityType = $params['entityType'];
        }
        $entityId = null;
        if (isset($params['entityId'])) {
            $entityId = $params['entityId'];
        }
        $actionId = null;
        if (isset($params['actionId'])) {
            $actionId = $params['actionId'];
        }

        $action = null;
        if (isset($params['action'])) {
            $action = $params['action'];
        }

        $privacy = null;
        $findActivity = true;
        if ($entityType == 'friend_add') {
            $privacy = self::$PRIVACY_FRIENDS_ONLY;
        } else if ($feedType == 'user') {
            $privacy = $this->setPrivacy($feedId);
        } else if (($entityType == 'photo_comments' || $entityType == 'multiple_photo_upload') && isset($_REQUEST['statusPrivacy'])) {
            if ($entityType == 'photo_comments') {
                $tempPhoto = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($entityId);
                $albumId = null;
                if ($tempPhoto != null) {
                    $albumId = $tempPhoto->albumId;
                }
                if ($albumId) {
                    $privacyOfAlbum = $this->getPrivacyOfAlbum($albumId);
                    if ($privacyOfAlbum != null) {
                        $privacy = $privacyOfAlbum;
                    }
                    $results = $this->updatePrivacyOfPhotosByAlbumId($albumId, $privacy);
                    $this->updateNewsFeedActivitiesByActionIds($results['actionId'], $privacy);
                    $findActivity = false;
                }
            } else if ($entityType == 'multiple_photo_upload') {
                $photoSampleId = null;
                $photoIdList = null;
                if (isset($event->getData()['photoIdList'])) {
                    $photoIdList = $event->getData()['photoIdList'];
                }

                $privacy = $this->validatePrivacy($_REQUEST['statusPrivacy']);
                if ($photoIdList != null && !isEmpty($photoIdList)) {
                    $photoSampleId = $photoIdList[0];
                } else {
                    $actionObj = NEWSFEED_BOL_Service::getInstance()->findAction('multiple_photo_upload', $entityId);
                    if ($actionObj != null) {
                        $data = $actionObj->data;
                        if ($data != null && isset(json_decode($data)->photoIdList[0])) {
                            $photoSampleId = json_decode($data)->photoIdList[0];
                        }
                    }
                }

                if ($photoSampleId != null) {
                    $albumId = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoSampleId)->albumId;
                    $privacyOfAlbum = $this->getPrivacyOfAlbum($albumId);
                    if ($privacyOfAlbum != null) {
                        $privacy = $privacyOfAlbum;
                    }
                    $results = $this->updatePrivacyOfPhotosByAlbumId($albumId, $privacy);
                    $this->updateNewsFeedActivitiesByActionIds($results['actionId'], $privacy);
                    $findActivity = false;
                }
            }
        } else if ($entityType == 'video_comments' && isset($_REQUEST['statusPrivacy'])) {
            $privacy = $this->validatePrivacy($_REQUEST['statusPrivacy']);
            $this->changePrivacyOfVideo($entityId, $privacy);
        } else if ($entityType == 'add_audio') {
            $privacy = $this->validatePrivacy($_REQUEST['statusPrivacy']);
        }

        if ($actionId != null && $privacy != null && $findActivity) {
            $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findIdListByActionIds(array($actionId));
            foreach ($activities as $activityId) {
                $activity = NEWSFEED_BOL_Service::getInstance()->findActivity($activityId)[0];
                $privacy = $this->validatePrivacy($privacy);
                $activity->privacy = $privacy;
                NEWSFEED_BOL_Service::getInstance()->saveActivity($activity, $action);
            }
        }
    }

    public function changePrivacyOfVideo($clipId, $privacy)
    {
        if (class_exists('VIDEO_BOL_ClipService')) {
            $clip = VIDEO_BOL_ClipService::getInstance()->findClipById($clipId);
            $clip->privacy = $privacy;
            VIDEO_BOL_ClipService::getInstance()->saveClip($clip);
        }
    }

    public function validatePrivacy($privacy)
    {
        if ($privacy == self::$PRIVACY_EVERYBODY || $privacy == self::$PRIVACY_ONLY_FOR_ME || $privacy == self::$PRIVACY_FRIENDS_ONLY) {
            return $privacy;
        }
        return self::$PRIVACY_ONLY_FOR_ME;
    }

    public function onAfterUpdateStatusFormRenderer(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['form']) && isset($params['component'])) {
            $form = $params['form'];
            if ($form->getElement('statusPrivacy') != null) {
                $params['component']->assign('statusPrivacyField', true);
            } else {
                $profileOwner = $this->findUserByProfile();
                if ($profileOwner != null && $profileOwner->getId() != OW::getUser()->getId()) {
                    $text = $this->getPrivacyStatusProfileLabel($profileOwner->getId(), $profileOwner->username);
                    $params['component']->assign('statusPrivacyLabel', $text);
                }
            }
        }
    }

    public function getPrivacyStatusProfileLabel($userId, $username)
    {
        $profileOwnerPrivacy = $this->getActionValueOfPrivacy('other_post_on_feed_newsfeed', $userId);
        $text = '';
        if ($profileOwnerPrivacy == self::$PRIVACY_ONLY_FOR_ME) {
            $text = OW::getLanguage()->text('frmsecurityessentials', 'show_to_user', array('username' => BOL_UserService::getInstance()->getDisplayName($userId)));
        } else if ($profileOwnerPrivacy == self::$PRIVACY_FRIENDS_ONLY) {
            $text = OW::getLanguage()->text('frmsecurityessentials', 'show_to_friends', array('username' => $username));
        } else if ($profileOwnerPrivacy == self::$PRIVACY_EVERYBODY) {
            $text = OW::getLanguage()->text('frmsecurityessentials', 'show_to_everybody');
        }
        return $text;
    }

    public function onBeforeUpdateStatusFormRenderer(OW_Event $event)
    {
        $params = $event->getParams();
        $user = $this->findUserByProfile();
        if (isset($params['form']) && ($user == null || ($user->getId() == OW::getUser()->getId())) && $params['form']->getElement('feedType')->getValue() == 'user') {
            $form = $params['form'];
            $form->addElement($this->createStatusPrivacyElement('my_post_on_feed_newsfeed'));
        }
    }

    public function onBeforeObjectRenderer(OW_Event $event)
    {
        $params = $event->getParams();
        if (OW::getUser()->isAuthenticated() && OW::getUser()->isAdmin()) {
            return;
        }
        if (isset($params['privacy']) && isset($params['ownerId'])) {
            $this->checkPrivacyOfObject($params['privacy'], $params['ownerId']);
        }
    }


    public function onCheckObjectBeforeSaveOrUpdate(OW_Event $event)
    {
        if (defined('OW_CRON')) {
            return true;
        }
        $params = $event->getParams();
        $isValid = true;
        if (isset($params['entity']) && isset($params['entityClass'])) {
            $entity = $params['entity'];
            if ($entity instanceof NEWSFEED_BOL_Status || $entity instanceof NEWSFEED_BOL_ActionFeed) {
                if (strcmp('groups', $entity->feedType) == 0) {
                    $isValid = $this->groupsNewsFeedCheckObjectBeforeSaveOrUpdate($entity->feedId);
                } else if (strcmp('user', $entity->feedType) == 0) {
                    $isValid = $this->userNewsFeedCheckObjectBeforeSaveOrUpdate($entity->feedId);
                }
            } else if ($entity instanceof BOL_Comment) {
                $isValid = $this->commentCheckObjectBeforeSaveOrUpdate($entity->commentEntityId);
            }
        }

        if (!$isValid) {
            exit(json_encode(array('error' => 'Save or update is not authorized')));
        }
    }

    public function groupsNewsFeedCheckObjectBeforeSaveOrUpdate($groupId)
    {

        if (!OW::getUser()->isAuthenticated()) {
            trigger_error('OW::getUser()->isAuthenticated() return false. function: groupsNewsFeedCheckObjectBeforeSaveOrUpdate.' . ' $groupId: ' . $groupId, E_USER_ERROR);
            return false;
        }
        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if (empty($groupDto)) {
            trigger_error('group does not exist return false. function: groupsNewsFeedCheckObjectBeforeSaveOrUpdate.' . ' $groupId: ' . $groupId, E_USER_ERROR);
            return false;
        }

        $isUserInGroup = GROUPS_BOL_Service::getInstance()->findUser($groupId, OW::getUser()->getId());
        $creatorId = $groupDto->userId;
        if (!$isUserInGroup && $creatorId != OW::getUser()->getId() &&
            !GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($groupDto) && !OW::getUser()->isAdmin()) {
            trigger_error('user can not edit or not admin return false. function: groupsNewsFeedCheckObjectBeforeSaveOrUpdate.' . ' $groupId: ' . $groupId . ' $creatorId: ' . $creatorId . ' currentUser: ' . OW::getUser()->getId(), E_USER_ERROR);
            return false;
        }
        return true;
    }

    public function userNewsFeedCheckObjectBeforeSaveOrUpdate($userId)
    {
        if (!OW::getUser()->isAuthenticated()) {
            return false;
        }
        
        if ($userId != OW::getUser()->getId() && !OW::getUser()->isAdmin()) {

            $isBloacked = BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $userId);

            if (OW::getUser()->isAuthorized('base', 'add_comment')) {
                if ($isBloacked) {
                    trigger_error('$isBloacked return false. function: userNewsFeedCheckObjectBeforeSaveOrUpdate' . 'userId: ' . $userId . ' currentUser: ' . OW::getUser()->getId(), E_USER_ERROR);
                    return false;
                } else {
                    $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_UPDATE_STATUS_FORM_CREATE_IN_PROFILE, array('userId' => $userId)));
                    if (isset($event->getData()['showUpdateStatusForm'])) {
                        if (!$event->getData()['showUpdateStatusForm']) {
                            trigger_error('showUpdateStatusForm return false. function: userNewsFeedCheckObjectBeforeSaveOrUpdate.' . 'userId: ' . $userId . ' currentUser: ' . OW::getUser()->getId(), E_USER_ERROR);
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    public function commentCheckObjectBeforeSaveOrUpdate($commentEntityId)
    {

        if (!OW::getUser()->isAuthenticated()) {
            trigger_error('OW::getUser()->isAuthenticated() return false. function: commentCheckObjectBeforeSaveOrUpdate.' . ' $commentEntityId: ' . $commentEntityId, E_USER_ERROR);
            return false;
        }
        if (OW::getUser()->isAdmin()) {
            return true;
        }
        $commentEntity = BOL_CommentEntityDao::getInstance()->findById($commentEntityId);
        if ($commentEntity == null)
            return false;
        $entityId = $commentEntity->entityId;
        $entityType = $commentEntity->entityType;

        $action = NEWSFEED_BOL_Service::getInstance()->findAction($entityType, $entityId);
        if ($action != null) {
            // there is a newsfeed action attached to it
            // checking create activity privacy
            $ownerId = $this->getActionOwner($action->id);
            $privacy = $this->getActionPrivacyByActionId($action->id);
            if (!isset($privacy) || !isset($ownerId)) {
                trigger_error('privacy or ownerId is not set. $ownerId: ' . $ownerId . ' $privacy: ' . $privacy . ' return false. function: commentCheckObjectBeforeSaveOrUpdate.' . ' $commentEntityId: ' . $commentEntityId, E_USER_ERROR);
                return false;
            }
            return $this->checkPrivacyOfObject($privacy, $ownerId);
        } else {
            if ($entityType == 'photo_comments') {
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($entityId);
                if (isset($photo)) {
                    $ownerId = PHOTO_BOL_PhotoService::getInstance()->findPhotoOwner($entityId);
                    return $this->checkPrivacyOfObject($photo->privacy, $ownerId);
                }
            } else if ($entityType == 'video_comments') {
                $video = VIDEO_BOL_ClipService::getInstance()->findClipById($entityId);
                if (isset($video)) {
                    $ownerId = VIDEO_BOL_ClipService::getInstance()->findClipOwner($entityId);
                    return $this->checkPrivacyOfObject($video->privacy, $ownerId);
                }
            } else if ($entityType == 'question') {
                $item = QUESTIONS_BOL_Service::getInstance()->findQuestion($entityId);
                if (isset($item)) {
                    return QUESTIONS_BOL_Service::getInstance()->canUserView($item);
                }
            } else if ($entityType == 'event') {
                return EVENT_BOL_EventService::getInstance()->canUserView($entityId, OW::getUser()->getId());
            } else if ($entityType == 'group' || $entityType == 'groups_wal') {
                $item = GROUPS_BOL_Service::getInstance()->findGroupById($entityId);
                if (isset($item)) {
                    return GROUPS_BOL_Service::getInstance()->isCurrentUserCanView($item);
                }
            } else if ($entityType == 'blog-post') {
                $item = PostService::getInstance()->findById($entityId);
                if (isset($item)) {
                    return $this->checkPrivacyOfObject($item->privacy, $item->authorId);
                }
            } else if ($entityType == 'news-entry') {
                $item = EntryService::getInstance()->findById($entityId);
                if (isset($item)) {
                    return $this->checkPrivacyOfObject($item->privacy, $item->authorId);
                }
            } else if (in_array($entityType, array(
                'user-status', 'user_join', 'forum-topic', 'multiple_photo_upload'))) {
                // newsfeed action for entity is deleted.
                OW::getLogger()->writeLog(OW_Log::ERROR,'newsfeed action for entity is deleted. return false. function: commentCheckObjectBeforeSaveOrUpdate.' . '$commentEntityId: ' . $commentEntityId);
                return false;
            } else {
                // no action was attached to it from the beginning
                return true;
            }
        }
        trigger_error('unknown entity type. return false. entitytype: ' . $entityType . ' function: commentCheckObjectBeforeSaveOrUpdate.', E_USER_ERROR);
        return false;
    }


    public function onBeforeFeedItemRenderer(OW_Event $event)
    {
        $params = $event->getParams();
        $throwEx = true;
        if(isset($params['throwEx'])){
            $throwEx = $params['throwEx'];
        }
        if (isset($params['actionId']) && isset($params['feedId'])) {
            $action = null;
            if (isset($params['action'])) {
                $action = $params['action'];
            }
            if ($action == null) {
                $action = NEWSFEED_BOL_Service::getInstance()->findActionById($params['actionId']);
            }
            $hasAccess = false;
            if ($action != null) {
                $actionData = json_decode($action->data);
                if (isset($actionData->contextFeedType) && $actionData->contextFeedType == 'groups') {
                    if (FRMSecurityProvider::checkPluginActive('groups', true)) {
                        $groupId = $actionData->contextFeedId;
                        $selectedGroup = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
                        if ($selectedGroup != null) {
                            $isUserInSelectedGroup = GROUPS_BOL_Service::getInstance()->findUser($groupId, OW::getUser()->getId());
                            if ($isUserInSelectedGroup != null) {
                                $hasAccess = true;
                            }
                        }
                    }
                }
            }

            if (!$hasAccess) {
                $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findIdListByActionIds(array($params['actionId']));
                foreach ($activities as $activityId) {
                    $activity = NEWSFEED_BOL_Service::getInstance()->findActivity($activityId)[0];
                    if ($activity->activityType == 'create') {
                        $this->checkPrivacyOfObject($activity->privacy, $params['feedId'], $activity->userId, $throwEx);
                    }
                }
            }
        } else if (isset($params['actionId']) && !isset($params['feedId'])) {
            //view feed page
            $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findIdListByActionIds(array($params['actionId']));
            foreach ($activities as $activityId) {
                $activity = NEWSFEED_BOL_Service::getInstance()->findActivity($activityId)[0];
                if ($activity->activityType == 'create') {
                    $actionFeed = NEWSFEED_BOL_Service::getInstance()->findFeedListByActivityids(array($activity->id));
                    if (isset($actionFeed[$activity->id]) && isset($actionFeed[$activity->id][0])) {
                        $this->checkPrivacyOfObject($activity->privacy, $actionFeed[$activity->id][0]->feedId, $activity->userId, $throwEx, $actionFeed[$activity->id][0]->feedType);
                    } else {
                        $this->checkPrivacyOfObjectForViewer($activity->privacy, $activity->userId, $throwEx);
                    }
                }
            }
        }
    }

    public function checkPrivacyOfObject($privacy, $ownerId, $activityOwner = null, $throwEx = true, $type = null)
    {
        if (OW::getUser()->isAuthenticated() && $ownerId == OW::getUser()->getId()) {
            return true;
        } else if (isset($type) && $type == 'user' && OW::getUser()->isAuthorized('newsfeed')) {
            return true;
        }  else if (isset($type) && $type == 'profile_user' && (OW::getUser()->isAuthorized('newsfeed') || OW::getUser()->isAuthorized('base','edit_user_profile'))) {
            return true;
        } else if ($privacy == self::$PRIVACY_EVERYBODY || ($activityOwner != null && OW::getUser()->isAuthenticated() && $activityOwner == OW::getUser()->getId())) {
            if (isset($type) && $type == 'groups') {
                if (FRMSecurityProvider::checkPluginActive('groups', true)) {
                    $group = GROUPS_BOL_Service::getInstance()->findGroupById($ownerId);
                    if (isset($group)) {
                        $canView = GROUPS_BOL_Service::getInstance()->isCurrentUserCanView($group, false);
                        if (!$canView) {
                            throw new Redirect404Exception();
                        }
                    }
                }
            }
            return true;
        } else if ($privacy == self::$PRIVACY_ONLY_FOR_ME && $ownerId != OW::getUser()->getId()) {
            if ($throwEx) {
                throw new Redirect404Exception();
            } else {
                return false;
            }
        } else if ($privacy == self::$PRIVACY_FRIENDS_ONLY && $ownerId != OW::getUser()->getId()) {
            $ownerFriendsId = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => $ownerId));
            if (!in_array(OW::getUser()->getId(), $ownerFriendsId)) {
                if ($throwEx) {
                    throw new Redirect404Exception();
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
    }

    public function checkPrivacyOfObjectForViewer($privacy, $ownerId, $throwEx)
    {
        if ((OW::getUser()->isAuthenticated() && $ownerId == OW::getUser()->getId()) || $privacy == self::$PRIVACY_EVERYBODY) {
            return true;
        } else if (OW::getUser()->isAuthenticated() && $privacy == self::$PRIVACY_ONLY_FOR_ME) {
            if ($throwEx) {
                throw new Redirect404Exception();
            } else {
                return false;
            }
        } else if (OW::getUser()->isAuthenticated() && $privacy == self::$PRIVACY_FRIENDS_ONLY && FRMSecurityProvider::checkPluginActive('friends', true)) {
            $ownerFriendsId = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => $ownerId));
            if (!in_array(OW::getUser()->getId(), $ownerFriendsId)) {
                if ($throwEx) {
                    throw new Redirect404Exception();
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else if (!OW::getUser()->isAuthenticated() && $privacy != self::$PRIVACY_EVERYBODY) {
            if ($throwEx) {
                throw new Redirect404Exception();
            } else {
                return false;
            }
        }
        return true;
    }

    public function onCollectPhotoContextActions(BASE_CLASS_EventCollector $event)
    {
        $params = $event->getParams();
        $photoId = $params['photoId'];

        if (OW::getUser()->isAuthenticated() && PHOTO_BOL_PhotoService::getInstance()->findPhotoOwner($photoId) == OW::getUser()->getId()) {
            $change_privacy_label = OW::getLanguage()->text('frmsecurityessentials', 'change_privacy_label');
            $change_privacy_of_album_label = OW::getLanguage()->text('frmsecurityessentials', 'change_privacy_of_album_label');

            $changePrivacyData = array(
                'url' => 'javascript:showAjaxFloatBoxForChangePrivacy(\'' . $photoId . '\', \'' . $change_privacy_label . '\',\'photo_comments\',\'\');',
                'id' => 'btn-video-change-privacy',
                'label' => $change_privacy_of_album_label,
                'order' => 4
            );

            $event->add($changePrivacyData);
        }
    }

    public function onCollectVideoToolbarItems(BASE_CLASS_EventCollector $event)
    {
        $params = $event->getParams();
        $clipId = $params['clipId'];
        $clipDto = $params['clipDto'];
        $change_privacy_label = OW::getLanguage()->text('frmsecurityessentials', 'change_privacy_label');
        $iconUrl = OW::getPluginManager()->getPlugin('frmsecurityessentials')->getStaticUrl() . 'images/' . $clipDto->privacy . '.svg';
        if (FRMSecurityProvider::themeCoreDetector())
            $iconUrl = OW::getPluginManager()->getPlugin('frmsecurityessentials')->getStaticUrl() . 'images/' . $clipDto->privacy . '.png';
        if (OW::getApplication()->getContext() == OW::CONTEXT_MOBILE) {
            $iconUrl = OW::getPluginManager()->getPlugin('frmsecurityessentials')->getStaticUrl() . 'images/' . $clipDto->privacy . '.png';
        }

        $isOwner = OW::getUser()->isAuthenticated() && $clipDto->userId == OW::getUser()->getId();
        if ($isOwner) {
            $changePrivacyData = array(
                'label' => '<div title="' . $this->getPrivacyLabelByFeedId($clipDto->privacy, $clipDto->userId) . '" class="feed_image_privacy '.  $clipDto->privacy .'" style="background-image:url(' . $iconUrl . ');" ></div>',
                'extraAttr' => 'class="owm_btn_change_privacy"'
            );
            $changePrivacyData['href'] = 'javascript:showAjaxFloatBoxForChangePrivacy(\'' . $clipId . '\', \'' . $change_privacy_label . '\',\'video_comments\',\'\');';
            $changePrivacyData['id'] = 'sec-' . $clipId . '-' . $clipDto->userId;
            $event->add($changePrivacyData);
        }
    }

    public function questionItemPrivacy(OW_Event $event)
    {
        $params = $event->getParams();
        $questionTpl = $event->getData();
        $questionId = $params['questionId'];
        $question = QUESTIONS_BOL_ActivityDao::getInstance()->findActivity($questionId, 'create', $questionId);
        if ($question->privacy != 'groups') {
            $privacyButtonString = $this->getPrivacyButtonInformation($questionId, $question->userId, $question->privacy, 'question');
            $questionTpl['privacy_label'] = $privacyButtonString;
        }
        $event->setData($questionTpl);
    }

    public function getPrivacyButtonInformation($objectId, $userId, $privacy, $objectType, $linkable = true)
    {
        $change_privacy_label = OW::getLanguage()->text('frmsecurityessentials', 'change_privacy_label');
        $privacyButton = array('label' => $this->getPrivacyLabelByFeedId($privacy, $userId),
            'imgSrc' => OW::getPluginManager()->getPlugin('frmsecurityessentials')->getStaticUrl() . 'images/' . $privacy . '.png');
        if ($objectType == "album") {
            $privacyButton = array_merge($privacyButton, array('WhiteImgSrc' => OW::getPluginManager()->getPlugin('frmsecurityessentials')->getStaticUrl() . 'images/' . $privacy . '.svg'));
        }
        if (OW::getUser()->isAuthenticated() && $userId == OW::getUser()->getId() && $linkable) {
            $privacyButton['onClick'] = 'javascript:showAjaxFloatBoxForChangePrivacy(\'' . $objectId . '\', \'' . $change_privacy_label . '\',\'' . $objectType . '\',\'' . $userId . '\')';
            $privacyButton['id'] = 'sec-' . $objectId . '-' . $userId;
        }
        $privacyButton['privacy'] = $privacy;
        return $privacyButton;
    }

    public function onBeforeDocumentRenderer(OW_Event $event)
    {
        $jsFile = OW::getPluginManager()->getPlugin('frmsecurityessentials')->getStaticJsUrl() . 'frmsecurityessentials.js';
        OW::getDocument()->addScript($jsFile);

        $cssFile = OW::getPluginManager()->getPlugin('frmsecurityessentials')->getStaticCssUrl() . 'frmsecurityessentials.css';
        OW::getDocument()->addStyleSheet($cssFile);
    }
    public function checkWarningAlert(OW_Event $event)
    {
        $warningAlertBanner=false;
        $warningAlertText='';
        $warningAlertIsSet=0;

        $warningAlertIsSet=OW::getConfig()->getValue('base', 'warningAlert');
        $warningAlertTimeStamp = OW::getLanguage()->text('admin', 'warningAlert_timeStamp');
        if ((!isset($_COOKIE['isWarningAlert']) || 
        $_COOKIE['isWarningAlert']!=$warningAlertTimeStamp )
         && $warningAlertIsSet && OW::getUser()->getId()){
            $warningAlertText = OW::getLanguage()->text('admin', 'warningAlert_text_value');
            $acceptBtn = OW::getLanguage()->text('frmsecurityessentials', 'accept');
            if($warningAlertIsSet==1 || $warningAlertIsSet==3){
            $warningAlertBanner=true;
            $options = array(
                'text' => $warningAlertText,
                'btn'=> $acceptBtn,
                'timeStamp' => $warningAlertTimeStamp,
           );
            $js = UTIL_JsGenerator::newInstance()->callFunction('add_warning_alert', array($options));
            OW::getDocument()->addOnloadScript($js);
            $cssFile = OW::getPluginManager()->getPlugin('frmsecurityessentials')->getStaticCssUrl() . 'warningAlert.css';
            OW::getDocument()->addStyleSheet($cssFile);
        }
            if($warningAlertIsSet==2 || $warningAlertIsSet==3){
             $js = "
             $.alert({
                title: '',
                content: `$warningAlertText`,
                buttons: {
                    $acceptBtn: {
                        label: 'Action 2',
                        classes: 'blueB',
                        action: function() {
                            var expires='';
                          document.cookie = 'isWarningAlert' + '=' + '$warningAlertTimeStamp'  + expires + '; path=/';
                        }
                    }
                }
            });
             ";
             OW::getDocument()->addOnloadScript($js);
            }
         }
        
    }

    public function onFeedItemRenderer(OW_Event $event)
    {
        $attr = OW::getRequestHandler()->getHandlerAttributes();
        $ctrlForCheck = array('NEWSFEED_CTRL_Feed', 'NEWSFEED_MCTRL_Feed');
        $actionForCheck = array('viewItem');
        $data = $event->getData();
        if (in_array($attr[OW_RequestHandler::ATTRS_KEY_CTRL], $ctrlForCheck) && in_array($attr[OW_RequestHandler::ATTRS_KEY_ACTION], $actionForCheck)) {
            if (isset($data['entityId']) && isset($data['entityType'])) {
                $this->hasNewsfeedAccessEntityType($data['entityType'], $data['entityId']);
            }
        }
        $params = $event->getParams();
        if (isset($params['data']) && isset($params['data']['privacy_label'])) {
            $data['privacy_label'] = $params['data']['privacy_label'];
            $event->setData($data);
        }
    }

    public function hasNewsfeedAccessEntityType($entityType, $entityId)
    {
        if (OW::getUser()->isAdmin()) {
            return true;
        }
        if ($entityType == 'event') {
            if (!class_exists('EVENT_BOL_EventService')) {
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_page_404'));
            }
            $eventDto = EVENT_BOL_EventService::getInstance()->findEvent($entityId);
            if ($eventDto === null) {
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_page_404'));
            }
            if ($eventDto->whoCanView == EVENT_BOL_EventService::CAN_VIEW_INVITATION_ONLY && !OW::getUser()->isAuthorized('event')) {
                if (!OW::getUser()->isAuthenticated()) {
                    OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_page_404'));
                }

                $eventInvite = EVENT_BOL_EventService::getInstance()->findEventInvite($eventDto->getId(), OW::getUser()->getId());
                $eventUser = EVENT_BOL_EventService::getInstance()->findEventUser($eventDto->getId(), OW::getUser()->getId());

                // check if user can view event
                if ((int)$eventDto->getWhoCanView() === EVENT_BOL_EventService::CAN_VIEW_INVITATION_ONLY && $eventUser === null && !OW::getUser()->isAuthorized('event')) {
                    if ($eventInvite === null) {
                        OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_page_404'));
                    }
                }
            }
        } else if ($entityType == 'group') {
            if (!class_exists('GROUPS_BOL_Service')) {
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_page_404'));
            }
            $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($entityId);

            if ($groupDto === null) {
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_page_404'));
            }

            if (!GROUPS_BOL_Service::getInstance()->isCurrentUserCanView($groupDto)) {
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_page_404'));
            }
            if ($groupDto->whoCanView == GROUPS_BOL_Service::WCV_INVITE && !OW::getUser()->isAuthorized('groups')) {
                if (!OW::getUser()->isAuthenticated()) {
                    OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_page_404'));
                }

                $invite = GROUPS_BOL_Service::getInstance()->findInvite($groupDto->id, OW::getUser()->getId());
                $user = GROUPS_BOL_Service::getInstance()->findUser($groupDto->id, OW::getUser()->getId());

                if ($groupDto->whoCanView == GROUPS_BOL_Service::WCV_INVITE && $user === null) {
                    if ($invite === null) {
                        OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_page_404'));
                    }
                }
            }
        }
    }

    public function onBeforeAlbumInfoRenderer(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['this']) && isset($params['album'])) {
            $album = $params['album'];
            $userId = $album->userId;
            $privacy = $this->getPrivacyOfAlbum($album->id);
            if ($privacy != null) {
                $params['this']->assign('privacy_label', $this->getPrivacyButtonInformation('', $userId, $privacy, '', false));
            }
        }
    }

    public function onBeforeAlbumsRenderer(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['this']) && isset($params['album'])) {
            $album = $params['album'];
            $privacy = $this->getPrivacyOfAlbum($album->id);
            if ($privacy != null) {
                $params['this']->assign('privacy_label', $this->getPrivacyButtonInformation($album->id, $album->userId, $privacy, 'album'));
            }
        }
    }

    public function onFeedItemRender(OW_Event $event)
    {
        $data = $event->getData();
        $params = $event->getParams();
        $feedType = $params['feedType'];
        $ignoreByEntityTypes = false;
        $entityTypeBlackList = array('friend_add', 'groups-status', 'group', 'group-join', 'event', 'groups-add-file');
        if (isset($params['action']['entityType']) && in_array($params['action']['entityType'], $entityTypeBlackList)) {
            $ignoreByEntityTypes = true;
        }
        if (isset($data['contextFeedType']) && $data['contextFeedType'] == 'groups') {
            if (isset($params['action']['entityType']) && $params['action']['entityType'] == 'forum-topic') {
                $ignoreByEntityTypes = true;
            }
        }
        if (isset($params['action']['feeds']) && sizeof($params['action']['feeds']) > 0 && isset($params['action']['feeds'][0]['feedType']) && $params['action']['feeds'][0]['feedType'] == 'groups') {
            $ignoreByEntityTypes = true;
        }
        if (in_array($feedType, array('user', 'my', 'site')) && !$ignoreByEntityTypes) {
            $activities = $params['activity'];
            foreach ($activities as $activity) {
                if ($activity['activityType'] == 'create') {
                    $feedObject = null;
                    $feedId = null;
                    if (isset($params['cache']['feed_by_creator_activity']) && array_key_exists($activity['id'], $params['cache']['feed_by_creator_activity'])) {
                        if (isset($params['cache']['feed_by_creator_activity'][$activity['id']])) {
                            $feedObject = $params['cache']['feed_by_creator_activity'][$activity['id']];
                            $feedId = $feedObject->feedId;
                        }
                    } else {
                        $feedObject = NEWSFEED_BOL_Service::getInstance()->findFeedListByActivityids(array($activity['id']));
                        if (isset($feedObject[$activity['id']])) {
                            $feedId = $feedObject[$activity['id']][sizeof($feedObject[$activity['id']]) - 1]->feedId;
                        }
                    }
                    if ($feedId != null) {
                        $data['privacy_label'] = $this->getPrivacyButtonInformation($params['createActivity']->actionId, $feedId, $activity['privacy'], 'user_status');
                    }
                }
            }
        }


        if (OW::getUser()->isAuthenticated()) {
            $activityIds = array();
            if (isset($params['action']) && in_array($params['action']['entityType'], array('group')) && class_exists('GROUPS_BOL_Service')) {
                foreach ($params['activity'] as $activity) {
                    if (in_array($activity['activityType'], array('groups-join', 'subscribe')) && $activity['userId'] == OW::getUser()->getId()) {
                        $activityIds[] = $activity['id'];
                    }
                }
            }

            if (isset($params['action']) && in_array($params['action']['entityType'], array('event')) && class_exists('EVENT_BOL_EventService')) {
                foreach ($params['activity'] as $activity) {
                    if (in_array($activity['activityType'], array('event-join', 'subscribe')) && $activity['userId'] == OW::getUser()->getId()) {
                        $activityIds[] = $activity['id'];
                    }
                }
            }

            if (sizeof($activityIds) > 0) {
                $acceptedActivityIds = array();
                $codes = array();
                foreach ($activityIds as $activityId) {
                    $feedList = NEWSFEED_BOL_Service::getInstance()->findFeedListByActivityids(array($activityId));
                    $feedId = null;
                    if (!empty($feedList) && isset($feedList[$activityId])) {
                        foreach ($feedList[$activityId] as $feed) {
                            if ($feed->feedType == 'user' && $feed->feedId == OW::getUser()->getId()) {
                                $acceptedActivityIds[] = $activityId;
                            }
                        }
                    }
                }
                if (sizeof($acceptedActivityIds) > 0) {
                    $activiIdsString = implode($acceptedActivityIds, '-');
                    $frmSecuritymanagerEvent = OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                        array('senderId' => OW::getUser()->getId(), 'receiverId' => $activiIdsString, 'isPermanent' => true, 'activityType' => 'delete_activity')));
                    if (isset($frmSecuritymanagerEvent->getData()['code'])) {
                        $code = $frmSecuritymanagerEvent->getData()['code'];
                    }
                    $data['contextMenu'] = empty($data['contextMenu']) ? array() : $data['contextMenu'];
                    $callbackUri = OW::getRequest()->getRequestUri();
                    $routUrl = OW::getRouter()->urlForRoute('frmsecurityessentials.delete_activity', array(
                        'activityId' => $activiIdsString
                    ));
                    if (isset($code)) {
                        $deleteUrl = OW::getRequest()->buildUrlQueryString($routUrl, array(
                            'redirectUri' => urlencode($callbackUri),
                            'code' => $code
                        ));
                    } else {
                        $deleteUrl = OW::getRequest()->buildUrlQueryString($routUrl, array(
                            'redirectUri' => urlencode($callbackUri)
                        ));
                    }
                    $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION, array('check' => true)));
                    if (isset($mobileEvent->getData()['isMobileVersion']) && $mobileEvent->getData()['isMobileVersion'] == true) {
                        array_unshift($data['contextMenu'], array(
                            'label' => OW::getLanguage()->text('frmsecurityessentials', 'delete_feed_item_label'),
                            'attributes' => array(
                                'onclick' => "return confirm_redirect($(this).data('confirm-msg'), '$deleteUrl');",
                                "data-confirm-msg" => OW::getLanguage()->text('frmsecurityessentials', 'delete_feed_item_confirmation')
                            )
                        ));
                    } else {
                        array_unshift($data['contextMenu'], array(
                            'label' => OW::getLanguage()->text('frmsecurityessentials', 'delete_feed_item_label'),
                            'url' => $deleteUrl,
                            'attributes' => array(
                                'data-message' => OW::getLanguage()->text('frmsecurityessentials', 'delete_feed_item_confirmation'),
                                'onclick' => "return confirm_redirect($(this).data().message), '$deleteUrl');"
                            )
                        ));
                    }
                }
            }
        }

        $event->setData($data);
    }

    public function editPrivacyProcess($privacy, $entityId, $actionType, $feedId, $action = null)
    {
        if (!OW::getUser()->isAuthenticated()) {
            return array('result' => false);
        }

        //checking csrf hash
        $params = $_POST;
        $event = new OW_Event('frmsecurityessentials.after.form.submission', $params);
        OW::getEventManager()->trigger($event);
        if (isset($event->getData()['not_allowed'])) {
            return array('result' => false);
        }

        $privacy = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->validatePrivacy($privacy);
        $actionId = null;
        $objectUserId = null;

        if ($actionType == 'user_status' || $actionType == 'group_status') {
            $actionId = $entityId;
            if ($action == null) {
                $action = NEWSFEED_BOL_Service::getInstance()->findActionById($actionId);
            }
            if ($action == null) {
                return array('result' => false);
            }
            $entityType = $action->entityType;
            if ($entityType == 'video_comments') {
                $objectUserId = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->updatePrivacyOfVideo($action->entityId, $privacy);
            } else if ($entityType == 'multiple_photo_upload') {
                $objectUserId = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->updatePrivacyOfMultiplePhoto(json_decode($action->data)->photoIdList, $privacy);
            } else if ($entityType == 'photo_comments') {
                if (FRMSECURITYESSENTIALS_BOL_Service::getInstance()->getActionOwner($actionId) == FRMSECURITYESSENTIALS_BOL_Service::getInstance()->getPhotoOwner($action->entityId)) {
                    $albumId = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($action->entityId)->albumId;
                    FRMSECURITYESSENTIALS_BOL_Service::getInstance()->updatePrivacyOfPhotosByAlbumId($albumId, $privacy);
                    $objectUserId = $this->updatePrivacyOfPhoto($action->entityId, $privacy);

                }
            } else if ($entityType == 'question') {
                $this->setQuestionsPrivacy($action->entityId, $privacy);
                $questionsActivities = QUESTIONS_BOL_ActivityDao::getInstance()->findByQuestionId($action->entityId);
                foreach ($questionsActivities as $questionsActivity) {
                    $questionsActivity->privacy = $privacy;
                    QUESTIONS_BOL_ActivityDao::getInstance()->saveItem($questionsActivity);
                }
            }
            if (OW::getPluginManager()->isPluginActive('frmquestions')) {
                $content = json_decode($action->data, true);
                if (isset($content['question_id']) && !empty($content['question_id'])) {
                    /** @var FRMQUESTIONS_BOL_Question $question */
                    $question = FRMQUESTIONS_BOL_QuestionDao::getInstance()->findById($content['question_id']);
                    if (!isset($question)) {
                        return array('result' => false);
                    }
                    $question->privacy = $privacy;
                    FRMQUESTIONS_BOL_QuestionDao::getInstance()->save($question);
                }
            }
        } else if ($actionType == 'video_comments') {
            $objectUserId = $this->updatePrivacyOfVideo($entityId, $privacy);
            if (class_exists('NEWSFEED_BOL_Service')) {
                $tempAction = NEWSFEED_BOL_Service::getInstance()->findAction($actionType, $entityId);
                if ($tempAction != null) {
                    $actionId = $tempAction->id;
                }
            }
        } else if ($actionType == 'album') {
            $result = $this->updatePrivacyOfPhotosByAlbumId($entityId, $privacy);
            $objectUserId = $result['userId'];
            $actionId = $result['actionId'];
        } else if ($actionType == 'questionsPrivacy') {
            $this->setProfileQuestionPrivacy($entityId, $privacy, $feedId);
            $actionId = null;
        } else if ($actionType == 'question') {
            if (class_exists('NEWSFEED_BOL_Service')) {
                $questionAction = NEWSFEED_BOL_Service::getInstance()->findAction($actionType, $entityId);
                if ($questionAction != null) {
                    $actionId = $questionAction->id;
                }
            }
            $questionsActivities = QUESTIONS_BOL_ActivityDao::getInstance()->findByQuestionId($entityId);
            foreach ($questionsActivities as $questionsActivity) {
                $questionsActivity->privacy = $privacy;
                QUESTIONS_BOL_ActivityDao::getInstance()->saveItem($questionsActivity);
            }
        }

        if ($objectUserId != null && ($feedId == null || $feedId == '')) {
            $feedId = $objectUserId;
        }

        if ($actionId != null && $feedId != null) {
            $this->updateNewsFeedActivitiesByActionIds($actionId, $privacy);
        }
        return array('result' => true,
            'title' => $this->getPrivacyLabelByFeedId($privacy, $feedId),
            'id' => '#sec-' . $entityId . '-' . $feedId,
            'src' => OW::getPluginManager()->getPlugin('frmsecurityessentials')->getStaticUrl() . 'images/' . $privacy . '.svg',
            'privacy' => $privacy,
            'privacy_list'=> array(self::$PRIVACY_EVERYBODY, self::$PRIVACY_FRIENDS_ONLY, self::$PRIVACY_ONLY_FOR_ME)
        );
    }

    public function deleteFeedItemByActivityId($activityIds = null)
    {
        if ($activityIds == null || !OW::getUser()->isAuthenticated() || !class_exists('NEWSFEED_BOL_Service') || !class_exists('NEWSFEED_BOL_ActionFeedDao')) {
            throw new Redirect404Exception();
        }

        $activityIdsArray = explode('-', $activityIds);
        $feedList = NEWSFEED_BOL_Service::getInstance()->findFeedListByActivityids($activityIdsArray);
        foreach ($activityIdsArray as $activityId) {
            if (isset($feedList[$activityId])) {
                foreach ($feedList[$activityId] as $feed) {
                    if ($feed->feedType == 'user' && $feed->feedId == OW::getUser()->getId()) {
                        NEWSFEED_BOL_ActionFeedDao::getInstance()->deleteByFeedAndActivityId('user', $feed->feedId, $activityId);
                    }
                }
            }
        }

        $redirectUri = urldecode($_GET['redirectUri']);
        OW_Application::getInstance()->redirect(OW_URL_HOME . $redirectUri);
    }

    public function onBeforeVideoRender(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['objectId']) && isset($params['this']) && isset($params['privacy']) && isset($params['userId'])) {
            $item = array();
            $item['privacy_label'] = $this->getPrivacyButtonInformation($params['objectId'], $params['userId'], $params['privacy'], 'video_comments');
            $params['this']->assign('item', $item);
        }
    }

    public function onBeforePhotoRender(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['objectId']) && isset($params['this']) && isset($params['privacy']) && isset($params['userId'])) {
            $item = array();
            $item['privacy_label'] = $this->getPrivacyButtonInformation($params['objectId'], $params['userId'], $params['privacy'], 'album');
            $params['this']->assign('item', $item);
        }
    }

    public function getPrivacyLabelByFeedId($privacy, $feedId)
    {
        $user = BOL_UserService::getInstance()->findUserById($feedId);
        if ($user == null) {
            return null;
        }
        return $this->getPrivacyLabel($privacy, $user->username);
    }

    public function getPrivacyLabel($privacy, $username)
    {
        if (self::$PRIVACY_FRIENDS_ONLY == $privacy) {
            return OW::getLanguage()->text('frmsecurityessentials', 'show_to_friends', array('username' => $username));
        } else if (self::$PRIVACY_ONLY_FOR_ME == $privacy) {
            return OW::getLanguage()->text('frmsecurityessentials', 'show_to_user', array('username' => $username));
        } else if (self::$PRIVACY_EVERYBODY == $privacy) {
            return OW::getLanguage()->text('frmsecurityessentials', 'show_to_everybody');
        }
    }

    public function onFeedCollectPrivacy(BASE_CLASS_EventCollector $event)
    {
        $event->add(array('*:*', 'view_my_feed'));
    }

    public function setPrivacy($ownerId)
    {
        $privacy = self::$PRIVACY_FRIENDS_ONLY;
        if ($ownerId != null && $ownerId == OW::getUser()->getId()) {
            if (isset($_REQUEST['statusPrivacy'])) {
                $privacy = $this->validatePrivacy($_REQUEST['statusPrivacy']);
            } else {
                $my_post_on_feed_newsfeed = $this->getActionValueOfPrivacy('my_post_on_feed_newsfeed', $ownerId);
                if ($my_post_on_feed_newsfeed != null) {
                    $privacy = $my_post_on_feed_newsfeed;
                }
            }
        } else if ($ownerId != null && $ownerId != OW::getUser()->getId()) {
            $other_post_on_feed_newsfeed = $this->getActionValueOfPrivacy('other_post_on_feed_newsfeed', $ownerId);
            if ($other_post_on_feed_newsfeed != null) {
                $privacy = $other_post_on_feed_newsfeed;
            }
        }
        return $privacy;
    }

    public function findUserByProfile()
    {
        $user = null;
        if (strpos($_SERVER['REQUEST_URI'], '/user/') !== false) {
            $username = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '/user/') + 6);
            if (strpos($username, '/') !== false) {
                $username = substr($username, 0, strpos($username, '/'));
            }
            $user = BOL_UserService::getInstance()->findByUsername($username);
        }
        return $user;
    }

    public function catchAllRequestsExceptions(BASE_CLASS_EventCollector $event)
    {
        $event->add(array(
            OW_RequestHandler::ATTRS_KEY_CTRL => 'BASE_CTRL_EmailVerify',
            OW_RequestHandler::ATTRS_KEY_ACTION => 'verify'
        ));

        $event->add(array(
            OW_RequestHandler::ATTRS_KEY_CTRL => 'BASE_CTRL_EmailVerify',
            OW_RequestHandler::ATTRS_KEY_ACTION => 'verifyForm'
        ));
    }

    public function onBeforeIndexStatusEnabled(OW_Event $event)
    {
        $params = $event->getParams();
        $config = OW::getConfig();
        $indexStatus = null;
        if ($config->configExists('newsfeed', 'index_status_enabled')) {
            $config->saveConfig('newsfeed', 'index_status_enabled', null);
        } else {
            $config->addConfig('newsfeed', 'index_status_enabled', null);
        }
        if (isset($params['checkBoxField'])) {
            $field = $params['checkBoxField'];
            $field->removeAttribute("checked");
            $field->addAttribute('disabled', 'disabled');
        }
    }

    /*
    * return the correct invitation feed status
    * @param OW_Event $event
    */
    public static function onBeforeFeedRendered(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['userId'])) {
            if ($params['userId'] == OW::getUser()->getId()) {
                FRMSecurityProvider::setStatusMessage(OW::getLanguage()->text('frmsecurityessentials', 'status_field_ownUser'));
            } else {
                $displayName = BOL_UserService::getInstance()->getDisplayName($params['userId']);
                FRMSecurityProvider::setStatusMessage(OW::getLanguage()->text('frmsecurityessentials', 'status_field_otherUser', array('username' => $displayName)));
            }
        } else {
            FRMSecurityProvider::setStatusMessage(OW::getLanguage()->text('frmsecurityessentials', 'status_field_invintation'));
        }
    }

    public function regenerateSessionID(OW_Event $event)
    {
        $userContext = null;
        if (OW::getSession()->isKeySet(OW_Application::CONTEXT_NAME)) {
            $userContext = OW::getSession()->get(OW_Application::CONTEXT_NAME);
        }
        OW::getSession()->regenerate();
        if ($userContext != null) {
            OW::getSession()->set(OW_Application::CONTEXT_NAME, $userContext);
        }
    }

    public function logoutIfIdle(OW_Event $event)
    {
        $deleteEvent = OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.before_checking_idle'));
        if(isset($deleteEvent->getData()['ignore']) && $deleteEvent->getData()['ignore']){
            return;
        }
        $user = OW::getUser();
        if (!$user->isAuthenticated() || $user->getUserObject() == null) {
            return;
        }
        $timestamp = $user->getUserObject()->getActivityStamp();
        $now = time();
        if (!OW::getSession()->isKeySet("frm_session_age")) {
            OW::getSession()->set("frm_session_age", OW::getConfig()->getValue('frmsecurityessentials', 'idleTime') * 60);
        }
        if ((!isset($_COOKIE['ow_login']) || !$_COOKIE['ow_login']) && $now - $timestamp > OW::getSession()->get('frm_session_age')) {
            OW::getUser()->logout();
            if (isset($_COOKIE['ow_login'])) {
                BOL_UserService::getInstance()->setLoginCookie('', null, time() - 3600);
            }
            OW::getSession()->set('no_autologin', true);
            OW::getApplication()->redirect(OW_URL_HOME);
        }
    }

    public function onAfterReadUrlEmbed(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['stringToFix'])) {
            $oneStepFixed = html_entity_decode($params['stringToFix'], ENT_NOQUOTES, 'UTF-8');
            $finalStepFixed = html_entity_decode($oneStepFixed, ENT_NOQUOTES, 'UTF-8');
            $finalStepFixed = htmlspecialchars_decode($finalStepFixed);
            $finalStepFixed = str_replace('&#x202B', " ", $finalStepFixed);
            $event->setData(array('fixedString' => $finalStepFixed));
        }
    }

    public function onCheckUrlEmbed(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['oembed']) && $params['oembed']['type'] == 'link') {
            if ($params['oembed']['title'] == null) {
                $event->setData(array('noContent' => true));
            }
        }
    }

    /* CSRF Hash creation */
    public function onBeforeFormCreation(OW_Event $event)
    {
        $data = $event->getData();
        $data['add_CSRF_hash'] = true;
        $event->setData($data);
    }

    /***
     * @param array<FormElement> $elements
     * @param $actionUrl
     * @return string
     */
    public function refresh_csrf_hash($elements, $actionUrl)
    {
        $form_elements = array();
        foreach ($elements as $element) {
            $form_elements[$element->getName()] = $element->getValue();
        }
        //$form_elements['actionUrl'] = $actionUrl;
        return $this->return_crypted_csrf_hash($form_elements, false);
    }

    /***
     * @param $form
     * @param $hash_recieved
     * @return string
     */
    private function return_crypted_csrf_hash($form, $hash_recieved = false)
    {
        $str = '';
        if (isset($form['form_name']) && $form['form_name'] == 'edit-privacy') { //for changing privacy settings
            $str = isset($form['objectId']) ? $str . $form['objectId'] : $str;
            $str = isset($form['feedId']) ? $str . $form['feedId'] : $str;
        } else if (isset($form['feedType']) && isset($form['feedId'])) { // for newsfeed status
            $str = $form['feedType'] . $form['feedId'];
        } else if (isset($form['entityType']) && isset($form['entityId'])) {
            $str = $form['entityType'] . $form['entityId'];
        } else if (isset($form['feedId'])) {
            $str = $form['feedId'];
        } else if (isset($form['id'])) {
            $str = $form['id'];
        } else if (isset($form['topic'])) { //for forum add post
            $str = $form['topic'];
        } else if (isset($form['chatId'])) { //for telegram
            $str = $form['chatId'];
        }
        $str = 'csrf_' . $form['csrf_token'] . '_' . $str;

        if ($hash_recieved == false) {
            // generate new hash
            $csrf_hash_expected = '';
            $frmSecuritymanagerEvent = OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId' => OW::getUser()->getId(), 'receiverId' => OW::getUser()->getId(), 'isPermanent' => true, 'activityType' => $str)));
            if (isset($frmSecuritymanagerEvent->getData()['code'])) {
                $csrf_hash_expected = $frmSecuritymanagerEvent->getData()['code'];
            }
            return $csrf_hash_expected;
        } else {
            // after form submit: check code
            $check_exists_event = OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array( 'code' => $hash_recieved, 'return_result' => true,
                    'senderId' => OW::getUser()->getId(), 'receiverId' => OW::getUser()->getId(), 'activityType' => $str
                )));
            return $check_exists_event->getData()['valid'];
        }
    }

    /***
     * @param OW_Event $event
     */
    public function onAfterFormSubmission(OW_Event $event)
    {
        $checkCSRF = true;
        $eventCheck = OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.before_csrf_token_check'));
        if (isset($eventCheck->getData()['not_check'])) {
            $checkCSRF = false;
        }
        if ( isset($_POST['ajaxFunc'] ) &&  $_POST['ajaxFunc'] == 'ajaxMoveToAlbum' ){
            $checkCSRF = false;
        }

        if ($checkCSRF) {
            $post = $event->getParams();
            unset($post['actionUrl']);
            if (!isset($post['csrf_token']) || !UTIL_Csrf::isTokenValid($post['csrf_token'])) {
                $data = $event->getData();
                $data['not_allowed'] = true;
                $event->setData($data);
            } else if (!isset($post['csrf_hash'])) {
                $data = $event->getData();
                $data['not_allowed'] = true;
                $event->setData($data);
            } else {
                $csrf_hash_received = $post['csrf_hash'];
                $csrf_hash_valid = $this->return_crypted_csrf_hash($post, $csrf_hash_received);

                if (!$csrf_hash_valid) {
                    $data = $event->getData();
                    $data['not_allowed'] = true;
                    $event->setData($data);
                }
            }
        }
    }
    /* End of CSRF Hash creation */


    /* CSRF for dynamic js forms */
    public function onBeforeDocumentRendererForJSCSRF(OW_Event $e)
    {
        $csrf = UTIL_Csrf::generateToken();

        $js = "var js_csrf = '$csrf'";
        OW::getDocument()->addScriptDeclarationBeforeIncludes($js);

        $js = "
function checkCSRF(formElem) {
    if(typeof $(formElem).attr('method') == \"undefined\" || $(formElem).attr('method')=='get'){
        return;
    }
    if(formElem==null){
        return;
    }
    var hasCsrfToken = false;
    var inputs = $('input[name=csrf_token]',formElem);
    if(inputs.length >0){
        $(inputs).each(function(){
            var csrfForm = this.closest('form');
            if(csrfForm!=null && csrfForm.id == formElem.id && csrfForm.name==formElem.name && csrfForm.action == formElem.action){
                hasCsrfToken = true;
            }
        });
    }
    if(!hasCsrfToken){
        $('<input />').attr('type', 'hidden')
          .attr('name', 'csrf_token')
          .attr('value', js_csrf)
          .appendTo(formElem);
    }
}
$(document).delegate('form', 'submit', function(){
    checkCSRF(this);
});
      ";
        OW::getDocument()->addOnloadScript($js);
    }

    public function onAfterRoute(OW_Event $e)
    {
        if (!isset($_POST) || empty($_POST) || OW::getRequest()->isAjax())
            return;
        if (!isset($_POST['csrf_token']) || !UTIL_Csrf::isTokenValid($_POST['csrf_token'])) {
            //invalid
            $event = OW::getEventManager()->trigger(new OW_Event('on.before.post.request.fail.for.csrf'));
            if (isset($event->getData()['pass'])) {
                return;
            }
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_page_404'));
        }
    }

    /* End of CSRF for dynamic js forms */

    /* Preventing HTML attributes and tags */
    /***
     * @param OW_Event $event
     */
    public function onBeforeHTMLStrip(OW_Event $event)
    {
        $params = $event->getParams();
        $ignoreAdmin = true;
        if (isset($params['ignoreAdmin'])) {
            $ignoreAdmin = $params['ignoreAdmin'];
        }
        if ($ignoreAdmin && OW::getUser()->isAuthenticated() && OW::getUser()->isAdmin()) {
            return;
        }
        if (isset($params['text'])) {
            $text = $event->getParams()['text'];

            //------FIX HTML TAGS
            if (strpos($text, '<') !== false) {
                //DomDocument
                $text = '<div>' . $text . '</div>';
                $doc = new DOMDocument();
                @$doc->loadHTML(mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8'));
                //$domDoc1 = preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $doc->saveHTML());

                # remove <!DOCTYPE
                $doc->removeChild($doc->doctype);
                # remove <html><body></body></html>
                $domDoc2 = "";
                $element = $doc->firstChild->firstChild->firstChild;
                $element = $this->secure_html_tag($element);
                $children = $element->childNodes;
                foreach ($children as $child) {
                    $domDoc2 .= $element->ownerDocument->saveHTML($child);
                }
                $text = $domDoc2;
                $event->setData(array('text' => $text));
            }

        }
    }

    /***
     * @param domElement $element
     * @return mixed
     */
    private function secure_html_tag($element)
    {
        if ($element->nodeType != XML_ELEMENT_NODE)
            return $element;

        // STEP 1: REMOVE WRONG STYLES
        $element->removeAttribute('class');
        if ($element->hasAttribute('style')) {
            $style = $element->getAttribute('style');
            $existingDeleteAttrList = array();
            $attrList = [
                'font',
                'font-family',
                'height',
                'position',
                'top',
                'bottom',
                'right',
                'left',
                'margin',
                'padding',
                'border',
                'box',
                'content',
                'visibility',
                'display',
                'clear',
                'float',
                'opacity',
                'cursor',
                'overflow',
                'z-index',
                'filter',
                'transition',
                'pointer',
                'important‍'];

            $style_parts = explode(';', $style);
            foreach ($attrList as $attr) {
                if (strpos($style, $attr) !== false)
                    $existingDeleteAttrList[] = $attr;
            }
            $new_attr_value = '';
            foreach ($style_parts as $style_part) {
                $attr_is_safe = true;
                foreach ($existingDeleteAttrList as $attr) {
                    if (strpos($style_part, $attr) !== false) {
                        $attr_is_safe = false;
                        break;
                    }
                }
                if ($attr_is_safe)
                    $new_attr_value .= $style_part . ';';
            }
            $element->setAttribute('style', $new_attr_value);
        }

        //STEP 2: REMOVE UNSECURE SOURCES
        if ($element->tagName == "img") {
            if ($element->hasAttribute('src')) {
                $img_src = $element->getAttribute('src');
                if (strpos($img_src, 'sign-out') !== false) // unauthorized urls
                {
                    return false;
                    //$img->removeAttribute('scr');
                }
                if (strpos($img_src, strstr(OW_URL_HOME, ':')) !== false) //local urls
                {
                    if (strpos($img_src, '/ow_userfiles/') === false && strpos($img_src, '/ow_static/') === false) {
                        return false;
                    }
                } else if (strpos($img_src, ":") === false) //local relative urls
                {
                    if (strpos($img_src, '/ow_userfiles/') === false && strpos($img_src, '/ow_static/') === false) {
                        return false;
                    }

                    $img_src = preg_replace_callback('/(^\.\/|^\.\.\/)/', function ($matches) {
                        return '/';
                    }, $img_src);
                    $img_src = preg_replace_callback('/(\.\/|\.\.\/)/', function ($matches) {
                        return '';
                    }, $img_src);
                    if (strpos($img_src, '/') !== 0)
                        $img_src = '/' . $img_src;
                    $img_src_full = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $img_src;
                    $urlExist = $this->checkUrlExist($img_src_full);
                    if ($urlExist === false) {
                        return false;
                    }
                }
                $element->setAttribute('src', $img_src);
            } else {
                return false;
            }
        }

        //STEP 3: ITERATE ON CHILDREN
        $children = $element->childNodes;
        for ($i = $children->length - 1; $i >= 0; $i--) {
            $child = $children->item($i);
            $newChild = $this->secure_html_tag($child);
            if (isset($newChild) && $newChild != false) {
                $element->replaceChild($newChild, $child);
            } else {
                $element->removeChild($child);
            }
        }

        return $element;
    }

    public function checkUrlExist($url)
    {
        $ctx = stream_context_create(array('http' =>
            array(
                'timeout' => 2,
            )
        ));
        $fileContent = OW::getStorage()->fileGetContent($url, true, false, $ctx);
        if ($fileContent === false) {
            return false;
        }

        return true;
    }

    /* End of Preventing HTML attributes and tags */

    public function deleteExpiredRequests()
    {
        FRMSECURITYESSENTIALS_BOL_RequestManagerDao::getInstance()->deleteExpiredRequests();
    }

    public function checkUsersSetPrivacy()
    {
        $numberOfUsers = BOL_UserService::getInstance()->count(true);
        $users = BOL_UserService::getInstance()->findList(0, $numberOfUsers, true);
        if (!FRMSecurityProvider::checkPluginActive('privacy', true)) {
            return;
        }
        $privacyService = PRIVACY_BOL_ActionService::getInstance();
        $updateNotification = (boolean)OW::getConfig()->getValue('frmsecurityessentials', 'privacyUpdateNotification');
        $pluginActive = FRMSecurityProvider::checkPluginActive('notifications', true);
        if ($updateNotification && $pluginActive) {
            foreach ($users as $user) {
                $userPrivacy = $privacyService->findAllUserPrivacy($user->getId());
                $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($user->getId()));
                $avatar = $avatars[$user->getId()];
                if (sizeof($userPrivacy) < 1) {
                    $event = new OW_Event('notifications.add', array(
                        'pluginKey' => 'frmsecurityessentials',
                        'entityType' => 'security-privacy_alert',
                        'entityId' => $user->getId(),
                        'action' => 'security-privacy_alert',
                        'userId' => $user->getId(),
                        'time' => time()
                    ), array(
                        'avatar' => $avatar,
                        'string' => array(
                            'key' => 'frmsecurityessentials+set_privacy_notification',
                            'vars' => array(
                                'url' => OW_Router::getInstance()->urlForRoute('privacy_index')
                            )
                        ),
                        'content' => '',
                        'url' => OW_Router::getInstance()->urlForRoute('privacy_index')
                    ));
                    OW::getEventManager()->trigger($event);
                    $notifService = NOTIFICATIONS_BOL_Service::getInstance();
                    $notification = $notifService->findNotification('security-privacy_alert', $user->getId(), $user->getId());
                    if (isset($notification)) {
                        $notification->sent = 0;
                        $notifService->saveNotification($notification);
                    }
                }
            }
        }
    }

    private function getUserUniqueToken(){
        if(OW::getSession()->isKeySet('user_unique_token')) {
            $unique_token = OW::getSession()->get('user_unique_token');
        }else{
            $unique_token = md5(UTIL_String::getRandomString(8, 5).time());
            OW::getSession()->set('user_unique_token', $unique_token);
        }
        return $unique_token;
    }

    private function getCodeFor($activityType, $senderId){
        return md5($this->getUserUniqueToken() . $activityType . $senderId );
    }

    public function onGenerateRequestManager(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['senderId']) && isset($params['isPermanent']) && isset($params['activityType'])) {
            $code = $this->getCodeFor( $params['activityType'], $params['senderId'] );
            $event->setData(array('code' => $code));
        }
    }

    public function onCheckRequestManager(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['senderId'])
            && isset($params['code'])
            && isset($params['activityType'])
        ){

            $validCode = $this->getCodeFor( $params['activityType'], $params['senderId'] );
            $valid = ($params['code'] === $validCode);

            if (isset($params['return_result']) && $params['return_result'] == true) {
                $event->setData(array('valid' => $valid));
            } else {
                if (!$valid) {
                    throw new Redirect404Exception();
                }
            }
        }else{
            OW::getLogger()->writeLog(OW_Log::ERROR, 'some parameters are missing', ["params" => $params, 'requestUrl' => $_SERVER['REQUEST_URI']]);
        }
    }

    public function onCheckGroupPrivacy(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['groupId'])) {
            $group = GROUPS_BOL_Service::getInstance()->findGroupById($params['groupId']);
            $private = $group->whoCanView == GROUPS_BOL_Service::WCV_INVITE;
            $visibility = $private
                ? 14 // VISIBILITY_FOLLOW + VISIBILITY_AUTHOR + VISIBILITY_FEED
                : 15; // Visible for all (15)
            $event->setData(array('private' => !$private, 'visibility' => $visibility));
        }
    }

    public function isValidSecurityCode($userId, $value)
    {
        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ($value === null || $user === null) {
            return false;
        }

        $questions = array();
        $questions[] = 'securityCode';
        $questionService = BOL_QuestionDataDao::getInstance();

        $securityCode = $questionService->findByQuestionsNameList($questions, $userId)[0];

        if ($securityCode->textValue === $value) {
            return true;
        }

        return false;
    }

    public function onCollectNotificationActions(BASE_CLASS_EventCollector $e)
    {
        $e->add(array(
            'section' => 'frmsecurityessentials',
            'action' => 'security-privacy_alert',
            'description' => OW::getLanguage()->text('frmsecurityessentials', 'email_notifications_alerts'),
            'selected' => true,
            'sectionLabel' => OW::getLanguage()->text('frmsecurityessentials', 'email_notification_alerts_label'),
            'sectionIcon' => 'ow_ic_write'
        ));
    }

    public function onRenderUserPrivacy(OW_Event $event)
    {
        $params = $event->getParams();
        if (!FRMSecurityProvider::checkPluginActive('privacy', true) || !isset($params['userId']) || !isset($params['controller'])) {
            return;
        }

        $privacyService = PRIVACY_BOL_ActionService::getInstance();

        $userPrivacy = $privacyService->findAllUserPrivacy($params['userId']);
        if (sizeof($userPrivacy) < 1) {
            $params['controller']->assign("notSetPrivacy", true);
        }
    }

    public function onAfterSignInFormCreated(OW_Event $event)
    {
        /** @var Form $form */
        $form = $event->getParams()['form'];
        $rememberMe = $form->getElement('remember');
        if (OW::getConfig()->configExists('frmsecurityessentials', 'remember_me_default_value')) {
            if (OW::getConfig()->getValue('frmsecurityessentials', 'remember_me_default_value')) {
                $rememberMe->setValue(true);
            } else {
                $rememberMe->setValue(false);
            }
        } else {
            $rememberMe->setValue(false);
        }
    }

    public function checkIpIsValid(OW_Event $event)
    {
        $adminEvent = OW::getEventManager()->call('admin.check_if_admin_page');
        if ($adminEvent) {
            $validIps = json_decode(OW::getConfig()->getValue('frmsecurityessentials', 'valid_ips'), true);
            if (!empty($validIps)) {
                $validIps = array_unique(preg_split('/\n/', $validIps[0]));
            }
            $ip = OW::getRequest()->getRemoteAddress();
            if ($ip == '::1' || empty($ip)) {
                $ip = '127.0.0.1';
            }
            if (!empty($validIps) && !in_array($ip, $validIps)) {
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_page_404'));
            }
        }
    }

    public function checkAccessUsersList(OW_Event $event)
    {
        if (!OW::getUser()->isAuthorized('frmsecurityessentials', 'view-users-list') && !OW::getUser()->isAdmin()) {
            throw new Redirect404Exception();
        }
    }

    public function addAllPluginUpdateButton(OW_Event $e)
    {
        $config = OW::getConfig();
        if ($config->configExists('frmsecurityessentials', 'update_all_plugins_activated') && $config->getValue('frmsecurityessentials', 'update_all_plugins_activated')) {
            $urlToRedirect = OW::getRouter()->urlForRoute("admin_plugins_installed");
            if (!empty($_GET['back_uri'])) {
                $urlToRedirect = OW_URL_HOME . urldecode($_GET['back_uri']);
            }
            $params = array("update_all" => true, "back-uri" => $urlToRedirect, "addParam" => UTIL_String::getRandomString());
            $url = OW::getRequest()->buildUrlQueryString(BOL_StorageService::getInstance()->getUpdaterUrl(), $params);
            $value = OW::getLanguage()->text("admin", "plugin_manual_update_all_button_label");
            $html = "<div class=\"ow_left\"><span class=\"ow_button\"><span class=\" ow_positive\"><input type=\"button\" value=\"" . $value . "\" class=\"ow_positive\" onclick=\"window.location=\'" . $url . "\'\"></span></span></div>";
            $js = "$('.updates_button').append('" . $html . "');";
            OW::getDocument()->addOnloadScript($js);
        }
    }

    public function validateFileField(OW_Event $event)
    {
        $params = $event->getParams();
        $data = array();
        if ($event->getData() != null) {
            $data = $event->getData();
        }
        if($params['field'] instanceof Textarea || $params['field'] instanceof TextField || $params['field'] instanceof WysiwygTextarea)
        {
            /**
             * remove unicode emoji characters
             */
            $removeUnicodeEmoji= new OW_Event('frm.remove.unicode.emoji', array('text' => $params['field']->getValue()));
            OW::getEventManager()->trigger($removeUnicodeEmoji);
            if(isset($removeUnicodeEmoji->getData()['correctedText'])) {
                $value = $removeUnicodeEmoji->getData()['correctedText'];
                $params['field']->setValue($value);
            }
        }
        if (get_class($params['field']) == 'FileField' && $params['field']->isRequired()
            && $params['field']->getAttribute('name') != null && $params['files'][$params['field']->getAttribute('name')] != null) {
            $data['validRequired'] = true;
        }
        $event->setData($data);
    }

    public function remove_emoji(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['text'] )) {
            $correctedText= preg_replace('/[\x{1F54A}][\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0077}\x{E006C}\x{E0073}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0073}\x{E0063}\x{E0074}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0065}\x{E006E}\x{E0067}\x{E007F})|[\x{1F3F4}](?:\x{200D}\x{2620}\x{FE0F})|[\x{1F3F3}](?:\x{FE0F}\x{200D}\x{1F308})|[\x{0023}\x{002A}\x{0030}\x{0031}\x{0032}\x{0033}\x{0034}\x{0035}\x{0036}\x{0037}\x{0038}\x{0039}](?:\x{FE0F}\x{20E3})|[\x{1F441}](?:\x{FE0F}\x{200D}\x{1F5E8}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F468})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F468})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B0})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2640}\x{FE0F})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FF}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FE}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FD}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FC}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FB}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F9B8}\x{1F9B9}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FF}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FE}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FD}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FC}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FB}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F9B8}\x{1F9B9}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{200D}\x{2642}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2695}\x{FE0F})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FF})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FE})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FD})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FC})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FB})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FA}](?:\x{1F1FF})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1FA}](?:\x{1F1FE})|[\x{1F1E6}\x{1F1E8}\x{1F1F2}\x{1F1F8}](?:\x{1F1FD})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F9}\x{1F1FF}](?:\x{1F1FC})|[\x{1F1E7}\x{1F1E8}\x{1F1F1}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1FB})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1FB}](?:\x{1F1FA})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FE}](?:\x{1F1F9})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FA}\x{1F1FC}](?:\x{1F1F8})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F7})|[\x{1F1E6}\x{1F1E7}\x{1F1EC}\x{1F1EE}\x{1F1F2}](?:\x{1F1F6})|[\x{1F1E8}\x{1F1EC}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}](?:\x{1F1F5})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EE}\x{1F1EF}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1F8}\x{1F1F9}](?:\x{1F1F4})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1F3})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F4}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FF}](?:\x{1F1F2})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F1})|[\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FD}](?:\x{1F1F0})|[\x{1F1E7}\x{1F1E9}\x{1F1EB}\x{1F1F8}\x{1F1F9}](?:\x{1F1EF})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EB}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F3}\x{1F1F8}\x{1F1FB}](?:\x{1F1EE})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1ED})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1EC})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F9}\x{1F1FC}](?:\x{1F1EB})|[\x{1F1E6}\x{1F1E7}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FB}\x{1F1FE}](?:\x{1F1EA})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1E9})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FB}](?:\x{1F1E8})|[\x{1F1E7}\x{1F1EC}\x{1F1F1}\x{1F1F8}](?:\x{1F1E7})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F6}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}\x{1F1FF}](?:\x{1F1E6})|[\x{00A9}\x{00AE}\x{203C}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}\x{21A9}-\x{21AA}\x{231A}-\x{231B}\x{2328}\x{23CF}\x{23E9}-\x{23F3}\x{23F8}-\x{23FA}\x{24C2}\x{25AA}-\x{25AB}\x{25B6}\x{25C0}\x{25FB}-\x{25FE}\x{2600}-\x{2604}\x{260E}\x{2611}\x{2614}-\x{2615}\x{2618}\x{261D}\x{2620}\x{2622}-\x{2623}\x{2626}\x{262A}\x{262E}-\x{262F}\x{2638}-\x{263A}\x{2640}\x{2642}\x{2648}-\x{2653}\x{2660}\x{2663}\x{2665}-\x{2666}\x{2668}\x{267B}\x{267E}-\x{267F}\x{2692}-\x{2697}\x{2699}\x{269B}-\x{269C}\x{26A0}-\x{26A1}\x{26AA}-\x{26AB}\x{26B0}-\x{26B1}\x{26BD}-\x{26BE}\x{26C4}-\x{26C5}\x{26C8}\x{26CE}-\x{26CF}\x{26D1}\x{26D3}-\x{26D4}\x{26E9}-\x{26EA}\x{26F0}-\x{26F5}\x{26F7}-\x{26FA}\x{26FD}\x{2702}\x{2705}\x{2708}-\x{270D}\x{270F}\x{2712}\x{2714}\x{2716}\x{271D}\x{2721}\x{2728}\x{2733}-\x{2734}\x{2744}\x{2747}\x{274C}\x{274E}\x{2753}-\x{2755}\x{2757}\x{2763}-\x{2764}\x{2795}-\x{2797}\x{27A1}\x{27B0}\x{27BF}\x{2934}-\x{2935}\x{2B05}-\x{2B07}\x{2B1B}-\x{2B1C}\x{2B50}\x{2B55}\x{3030}\x{303D}\x{3297}\x{3299}\x{1F004}\x{1F0CF}\x{1F170}-\x{1F171}\x{1F17E}-\x{1F17F}\x{1F18E}\x{1F191}-\x{1F19A}\x{1F201}-\x{1F202}\x{1F21A}\x{1F22F}\x{1F232}-\x{1F23A}\x{1F250}-\x{1F251}\x{1F300}-\x{1F321}\x{1F324}-\x{1F393}\x{1F396}-\x{1F397}\x{1F399}-\x{1F39B}\x{1F39E}-\x{1F3F0}\x{1F3F3}-\x{1F3F5}\x{1F3F7}-\x{1F3FA}\x{1F400}-\x{1F4FD}\x{1F4FF}-\x{1F53D}\x{1F549}-\x{1F54E}\x{1F550}-\x{1F567}\x{1F56F}-\x{1F570}\x{1F573}-\x{1F57A}\x{1F587}\x{1F58A}-\x{1F58D}\x{1F590}\x{1F595}-\x{1F596}\x{1F5A4}-\x{1F5A5}\x{1F5A8}\x{1F5B1}-\x{1F5B2}\x{1F5BC}\x{1F5C2}-\x{1F5C4}\x{1F5D1}-\x{1F5D3}\x{1F5DC}-\x{1F5DE}\x{1F5E1}\x{1F5E3}\x{1F5E8}\x{1F5EF}\x{1F5F3}\x{1F5FA}-\x{1F64F}\x{1F680}-\x{1F6C5}\x{1F6CB}-\x{1F6D2}\x{1F6E0}-\x{1F6E5}\x{1F6E9}\x{1F6EB}-\x{1F6EC}\x{1F6F0}\x{1F6F3}-\x{1F6F9}\x{1F910}-\x{1F93A}\x{1F93C}-\x{1F93E}\x{1F940}-\x{1F945}\x{1F947}-\x{1F970}\x{1F973}-\x{1F976}\x{1F97A}\x{1F97C}-\x{1F9A2}\x{1F9B0}-\x{1F9B9}\x{1F9C0}-\x{1F9C2}\x{1F9D0}-\x{1F9FF}]/u', '', $params['text']);
            $event->setData(array('correctedText'=>$correctedText));
        }
    }
    public function allowPageCustomizationByRole(OW_Event $event)
    {
        $params = $event->getParams();
        $data = array();
        $userId = OW::getUser()->getId();
        if (isset($params['place'])) {
            if ($params['place'] == 'profile') {
                $isAuthorizedForCustomization = OW::getAuthorization()->isUserAuthorized($userId, 'frmsecurityessentials', 'customize_user_profile');
                $data['allowed'] = $isAuthorizedForCustomization;
            }
        }
        $event->setData($data);
    }



    public function deleteCronJobs(OW_Event $event)
    {
        $params = $event->getParams();
        if (!isset ($params['pluginKey'])) {
            return;
        }
        $pluginKey = $params['pluginKey'];
        BOL_CronService::getInstance()->deleteJobsByPluginKey($pluginKey);
    }

    public function onBeforeDocumentRenderForIECheck(OW_Event $e)
    {
        $config = OW::getConfig();
        if ($config->configExists('frmsecurityessentials', 'ie_message_enabled') && !$config->getValue('frmsecurityessentials', 'ie_message_enabled')){
            return;
        }

        $text = OW::getLanguage()->text('frmsecurityessentials', 'ie_message');
        $text = str_replace("\n", ' ', $text);
        $js = "
        var cookieToday = new Date(); 
        var expiryDate = new Date(cookieToday.getTime() + (365 * 86400000)); // a year
        function setCookie (name,value,expires,path,theDomain,secure) { 
           value = escape(value);
           var theCookie = name + '=' + value + 
           ((expires)    ? '; expires=' + expires.toGMTString() : '') + 
           ((path)       ? '; path='    + path   : '') + 
           ((theDomain)  ? '; domain='  + theDomain : '') + 
           ((secure)     ? '; secure'            : ''); 
           document.cookie = theCookie;
        } 
        function getCookie(Name) { 
           var search = Name + '=' 
           if (document.cookie.length > 0) { // if there are any cookies 
              var offset = document.cookie.indexOf(search) 
              if (offset != -1) { // if cookie exists 
                 offset += search.length 
                 // set index of beginning of value 
                 var end = document.cookie.indexOf(';', offset) 
                 // set index of end of cookie value 
                 if (end == -1) end = document.cookie.length 
                 return unescape(document.cookie.substring(offset, end)) 
              } 
           } 
        } 
        
        var ua = window.navigator.userAgent;
        var msie = ua.indexOf('MSIE ');
        if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\\:11\\./))
        {
            var cookieValue = getCookie('ie_message_seen');
            if(cookieValue != '1')
            {
                $.alert({
                    title: '',
                    content: '$text',
                });
                setCookie('ie_message_seen', '1',expiryDate,'/');
            }
        }
        ";
        OW::getDocument()->addOnloadScript($js);
    }

    public function checkVerifyPeerPHPMailer( OW_Event $event )
    {
        $config=OW::getConfig();
        if($config->configExists('frmsecurityessentials' , 'disable_verify_peer') && $config->getValue('frmsecurityessentials' , 'disable_verify_peer')==true )
        {
            $event->setData(array('disable_verify_peer'=>true));
        }
    }

    public function checkUserAccessGetContents( OW_Event $event )
    {
        $config=OW::getConfig();
        if($config->configExists('frmsecurityessentials' , 'disable_user_get_other_sites_content') && $config->getValue('frmsecurityessentials' , 'disable_user_get_other_sites_content')==true )
        {
            if(!OW::getUser()->isAdmin()) {
                $event->setData(array('denied_access' => true));
            }
        }
    }


    public function actionDeleteUrl( OW_Event $event )
    {
        if(isset($event->getParams()['userId'])){
            $userId = $event->getParams()['userId'];
            $event->setData(array('href'=> OW::getRouter()->urlForRoute('frmsecurityessentials.delete_user', ['userId' => $userId])));
        }
        else if(isset($event->getParams()['users'])){
            $userId = json_encode($event->getParams()['users']);
            $event->setData(array('href'=> OW::getRouter()->urlForRoute('frmsecurityessentials.delete_user', ['userId' => $userId])));
        }
    }

    public function sendEmailToUser($subject, $message, $userIds){
        $configs = OW::getConfig()->getValues('base');
        $sendFromEmail = OW::getConfig()->getValue('base', 'site_email');
        $mailStateEvent = new OW_Event('base_before_email_create', array('adminNotificationUser' => $configs['mail_smtp_user']));
        OW::getEventManager()->trigger($mailStateEvent);
        if(isset($mailStateEvent->getData()['adminNotificationUser'])){
            $sendFromEmail = $mailStateEvent->getData()['adminNotificationUser'];
        }

        $mail = OW::getMailer()->createMail();
        foreach($userIds as $userId) {
            $sendToEmail = BOL_UserService::getInstance()->findUserById($userId)->getEmail();
            $mail->addRecipientEmail($sendToEmail);
        }
        $mail->setSender($sendFromEmail);
        $mail->setSenderSuffix(false);
        $mail->setSubject($subject);
        $mail->setTextContent($message);
        $mail->setHtmlContent($message);
        OW::getMailer()->send($mail);
    }

    /**
     * @param $controller
     * @throws Redirect404Exception
     */
    public function deleteUser($params)
    {
        $controllerData=array();
        $language = OW::getLanguage();

        if(empty($params['userId'])){
            $controllerData['redirect404Error']=true;
            return $controllerData;
        }

        if($params['userId'] == 'me'){
            if(!OW::getUser()->isAuthenticated()){
                $controllerData['redirect404Error']=true;
                return $controllerData;
            }
            $userIds = [OW::getUser()->getId()];
        }else{
            if(!OW::getUser()->isAuthenticated() || !(OW::getUser()->isAuthorized('base'))){
                $controllerData['redirect404Error']=true;
                return $controllerData;
            }
            $userId = $params['userId'];
            if ((int)$userId > 0){
                $userIds = [$userId];
            }else{
                $userIds = json_decode(urldecode($userId), true);
                $userIds = array_values($userIds);
            }
        }
        $usersInfo = [];
        foreach($userIds as $key => $userId) {
            $user = BOL_UserService::getInstance()->findUserById($userId);
            if (!isset($user) || $userId==1){
                continue;
            }
            $usersInfo[] = $user;
            $controllerData['component']["userInfo_".$user->getUsername()] =new BASE_CMP_UserInfo($user->getUsername());
        }
        if(count($usersInfo) == 0){
            $controllerData['redirect404Error']=true;
            return $controllerData;
        }
        $controllerData['assign']['userArray']= $usersInfo;

        // Form
        $form = new Form('form');
        $removedByAdmin = count($userIds) > 1 || $userIds[0] != OW::getUser()->getId();
        if( $removedByAdmin ) {
            $emailAdminsElement = new CheckboxField('email_admins');
            $emailAdminsElement->setLabel($language->text('frmsecurityessentials', 'email_all_admins'));
            $emailAdminsElement->setValue(true);
            $form->addElement($emailAdminsElement);

            $emailUsersElement = new CheckboxField('email_users');
            $emailUsersElement->setLabel($language->text('frmsecurityessentials', 'email_all_deleted_users'));
            $emailUsersElement->setValue(false);
            $form->addElement($emailUsersElement);
        }
        $controllerData['assign']['removedByAdmin']= $removedByAdmin;

        $password = new PasswordField('password');
        $password->setLabel($language->text("frmsecurityessentials", "your_password"));
        $password->setRequired(true);
        $password->setDescription($language->text("frmsecurityessentials", "your_password_desc"));
        $form->addElement($password);

        $fieldCaptcha = new CaptchaField('captcha');
        $fieldCaptcha->setLabel(OW::getLanguage()->text('base', 'captcha_value'));
        $form->addElement($fieldCaptcha);

        $editSubmit = new Submit('submit');
        $editSubmit->addAttribute('class', 'ow_delete_user_button');
        $editSubmit->setValue($language->text('base', 'delete_profile'));
        $form->addElement($editSubmit);

        $controllerData['form']=$form;

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            $auth = BOL_UserService::getInstance()->isValidPassword( OW::getUser()->getId(), $data['password'], true );
            if(!$auth){
                $controllerData['error']= true;
                return $controllerData;
            }

            $moderators = BOL_AuthorizationService::getInstance()->getModeratorList();

            foreach($usersInfo as $key => $user) {
                $currentUserName = BOL_UserService::getInstance()->findUserById(OW::getUser()->getId())->getUsername();
                $mailTitle = $language->text('frmsecurityessentials', 'user_is_removed_email_title');
                $mailContent = $language->text('frmsecurityessentials', 'user_is_removed_email_content',
                    [
                        'realName' => BOL_UserService::getInstance()->getDisplayName($user->getId()),
                        'username'=>$user->getUsername(),
                        'email'=>$user->getEmail(),
                        'adminRealName' => BOL_UserService::getInstance()->getDisplayName(OW::getUser()->getId()),
                        'adminUsername' => $currentUserName
                    ]);
                if(!$removedByAdmin || $data['email_admins']){
                    $moderatorUserIds = [];
                    foreach ( $moderators as $moderator ) {
                        $moderatorUserIds[] = $moderator->userId;
                    }
                    $this->sendEmailToUser($mailTitle, $mailContent, $moderatorUserIds);
                }
                if(!$removedByAdmin || $data['email_users']){
                    $this->sendEmailToUser($mailTitle, $mailContent, [$user->getId()]);
                }
                BOL_UserService::getInstance()->deleteUser($user->getId(), true);
            }
            $controllerData['success']=true;
        }
        return $controllerData;
    }
}