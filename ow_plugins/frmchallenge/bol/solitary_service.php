<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmchallenge.bol
 * @since 1.0
 */
class FRMCHALLENGE_BOL_SolitaryService
{
    private static $classInstance;
    private $challengeDao;
    private $categoryDao;
    private $userDao;
    private $questionDao;
    private $answerDao;
    private $userAnswerDao;
    private $solitaryDao;
    private $bookletDao;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->challengeDao = FRMCHALLENGE_BOL_ChallengeDao::getInstance();
        $this->categoryDao = FRMCHALLENGE_BOL_CategoryDao::getInstance();
        $this->userDao = FRMCHALLENGE_BOL_UserDao::getInstance();
        $this->questionDao = FRMCHALLENGE_BOL_QuestionDao::getInstance();
        $this->solitaryDao = FRMCHALLENGE_BOL_SolitaryDao::getInstance();
        $this->userAnswerDao = FRMCHALLENGE_BOL_UserAnswerDao::getInstance();
        $this->answerDao = FRMCHALLENGE_BOL_AnswerDao::getInstance();
        $this->bookletDao = FRMCHALLENGE_BOL_BookletDao::getInstance();
    }

    public function getAllCategoryLabel(){
        return OW::getLanguage()->text('frmchallenge', 'all_category');
    }

    public function getPublicSolitaryChallenges($userId){
        return $this->solitaryDao->getPublicSolitaryChallenges($userId);
    }

    public function canUserCreateSolitary(){
        if(!OW::getUser()->isAuthenticated()){
            return false;
        }

        if(!FRMCHALLENGE_BOL_GeneralService::getInstance()->isSolitaryChallengeEnable()){
            return false;
        }

        if(!OW::getUser()->isAuthorized('frmchallenge','add_solitary_challenge') && !OW::getUser()->isAdmin()){
            return false;
        }

        return true;
    }

    public function getPublicSolitaryChallengesInfo($userId){
        $result = array();

        $solitaries = $this->getPublicSolitaryChallenges($userId);
        foreach ($solitaries as $solitary){
            $data = FRMCHALLENGE_BOL_GeneralService::getInstance()->populateChallengeInfo($solitary->id, $userId, FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE);
            $result[] = $data;
        }

        return $result;
    }

    public function getWinSolitaryPoint(){
        return rand(100, 200);
    }

    public function getLoseSolitaryPoint(){
        return rand(20, 40);
    }

    public function getEqualSolitaryPoint(){
        return rand(40, 80);
    }

    public function checkCorrectChallengeType($name){
        return in_array($name, array(
            FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE,
            FRMCHALLENGE_BOL_GeneralService::GROUPS_TYPE,
            FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE));
    }

    public function checkUserCanDoChallengeWithUserId($userId, $opponentId){
        if(!$this->isFriendsPluginActive()){
            return true;
        }
        $service = FRIENDS_BOL_Service::getInstance();
        $isFriends = $service->findFriendship($userId, $opponentId);
        if (isset($isFriends) && $isFriends->status == 'active') {
            return true;
        }
        return false;
    }

    public function isFriendsPluginActive(){
        if(FRMSecurityProvider::checkPluginActive('friends', true)) {
            return true;
        }

        return false;
    }

    public function addSolitaryChallenge($data, $userId){
        $result['error'] = false;
        $result['message'] = "";
        $result['join'] = false;
        $result['solitaryId'] = null;

        $opponentId = null;
        if(isset($data['opponent_username']) && !empty($data['opponent_username'])){
            $user = BOL_UserService::getInstance()->findByUsername($data['opponent_username']);
            if($user == null){
                $result['error'] = true;
                $result['message'] = OW::getLanguage()->text('frmchallenge', 'user_not_found');
            }else if($this->checkUserCanDoChallengeWithUserId($userId, $user->getId())){
                $opponentId = $user->getId();
            }else{
                $result['error'] = true;
                $result['message'] = OW::getLanguage()->text('frmchallenge', 'user_not_found');
            }
        }

        if(!$result['error']) {
            $result = $this->findSolitaryChallenge($data, $userId);
            if(!$result['join']) {
                $result = $this->checkRepetitiveSolitaryChallenge($data, $userId, $opponentId);
                if (!$result['error']) {
                    $data['type'] = FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE;
                    $result = FRMCHALLENGE_BOL_GeneralService::getInstance()->addChallenge($data);
                    if (!$result['error']) {
                        $challenge = $result['challenge'];
                        $solitaryChallenge = new FRMCHALLENGE_BOL_Solitary();
                        $solitaryChallenge->userId = $userId;
                        $solitaryChallenge->opponentId = $opponentId;
                        $solitaryChallenge->challengeId = $challenge->id;
                        $this->solitaryDao->save($solitaryChallenge);
                        $result['solitaryChallenge'] = $solitaryChallenge;
                        if($opponentId != null){
                            $this->sendNotificationForRequestSolitaryChallenge($opponentId, $solitaryChallenge->id, $userId);
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function getCreateSolitaryChallengesByUserId($userId){
        return $this->solitaryDao->getCreateSolitaryChallengesByUserId($userId);
    }

    public function updateSeenBooklet($questionId, $challengeId, $isOpponent = false){
        $this->bookletDao->updateSeenBooklet($questionId, $challengeId, $isOpponent);
    }

    public function checkRepetitiveSolitaryChallenge($data, $userId, $opponentId = null){
        $result['error'] = false;
        $result['message'] = "";
        $categoryId = null;
        if(isset($data['categoryId']) && !empty($data['categoryId'])){
            $categoryId = $data['categoryId'];
        }


        $solitaryChallenge = null;

        $solitaryChallengesOfUser = $this->getCreateSolitaryChallengesByUserId($userId);

        foreach ($solitaryChallengesOfUser as $solitary){
            $challenge = $this->challengeDao->findById($solitary->challengeId);
            $isCategoryEqual = false;
            if($challenge->categories == null && $categoryId == null){
                $isCategoryEqual = true;
            }else if($challenge->categories != null && $categoryId != null){
                $categories = json_decode($challenge->categories);
                if(in_array($categoryId, $categories)){
                    $isCategoryEqual = true;
                }
            }

            if($isCategoryEqual && $solitary->opponentId == $opponentId){
                $result['error'] = true;
                $result['message'] = OW::getLanguage()->text('frmchallenge', 'create_same_challenge');
            }
        }

        return $result;
    }

    public function finishSolitaryChallenge($solitaryId){
        $solitary = $this->solitaryDao->findById($solitaryId);
        $challenge = $this->challengeDao->findById($solitary->challengeId);
        if($challenge->status != FRMCHALLENGE_BOL_GeneralService::STATUS_FINISH) {
            $challenge->status = FRMCHALLENGE_BOL_GeneralService::STATUS_FINISH;
            $this->challengeDao->save($challenge);

            $userId = $solitary->userId;
            $opponentId = $solitary->opponentId;

            if (FRMCHALLENGE_BOL_GeneralService::getInstance()->allQuestionsAnswered($challenge->id)) {
                $userPoint = FRMCHALLENGE_BOL_GeneralService::getInstance()->findPointOfUserInChallenge($solitaryId, FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE, $userId, $challenge->id);
                $opponentPoint = FRMCHALLENGE_BOL_GeneralService::getInstance()->findPointOfUserInChallenge($solitaryId, FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE, $opponentId, $challenge->id);
                $winPoint = $challenge->winPoint;
                $losePoint = $challenge->losePoint;
                $equalPoint = $challenge->equalPoint;
                if ($userPoint > $opponentPoint) {
                    FRMCHALLENGE_BOL_GeneralService::getInstance()->addUserPoint($userId, $winPoint);
                    FRMCHALLENGE_BOL_GeneralService::getInstance()->addUserPoint($opponentId, $losePoint);
                } else if ($userPoint < $opponentPoint) {
                    FRMCHALLENGE_BOL_GeneralService::getInstance()->addUserPoint($opponentId, $winPoint);
                    FRMCHALLENGE_BOL_GeneralService::getInstance()->addUserPoint($userId, $losePoint);
                } else {
                    FRMCHALLENGE_BOL_GeneralService::getInstance()->addUserPoint($userId, $equalPoint);
                    FRMCHALLENGE_BOL_GeneralService::getInstance()->addUserPoint($opponentId, $equalPoint);
                }
            }
        }
    }

    public function findPointOfUserInSolitaryChallenge($solitaryId, $userId){
        $solitary = $this->solitaryDao->findById($solitaryId);
        $booklets = FRMCHALLENGE_BOL_GeneralService::getInstance()->findBooklets($solitary->challengeId);
        $point = 0;
        foreach ($booklets as $booklet){
            $answerQuestion = false;
            if($solitary->userId == $userId && $booklet->userIdSeen == 1){
                $answerQuestion = true;
            }else if($solitary->opponentId == $userId && $booklet->opponentIdSeen == 1){
                $answerQuestion = true;
            }

            if($answerQuestion){
                $userAnswer = FRMCHALLENGE_BOL_GeneralService::getInstance()->findUserAnswer($booklet->questionId, $userId, $solitary->challengeId);
                $userAnswerId = $userAnswer->answerId;
                $correctAnswerId = FRMCHALLENGE_BOL_GeneralService::getInstance()->findCorrectAnswerOfQuestion($booklet->questionId);
                if($correctAnswerId == $userAnswerId){
                    $question = $this->questionDao->findById($booklet->questionId);
                    $point += $question->point;
                }
            }
        }

        return $point;
    }

    public function userCanCancelSolitaryChallenge($userId, $solitaryId){
        $solitary = $this->solitaryDao->findById($solitaryId);

        if($solitary == null || $solitary->opponentId == null) {
            return false;
        }

        if($solitaryId == null){
            return false;
        }

        $solitary = $this->solitaryDao->findById($solitaryId);
        if($solitary == null){
            return false;
        }

        if($solitary->opponentId == null){
            return false;
        }

        if($solitary->opponentId != $userId && $solitary->userId != $userId){
            return false;
        }

        $challenge = $this->challengeDao->findById($solitary->challengeId);

        if($challenge == null){
            return false;
        }

        if($challenge->status != FRMCHALLENGE_BOL_GeneralService::STATUS_PENDING){
            return false;
        }

        return true;
    }

    public function cancelSolitaryChallenge($userId, $solitaryId){
        $solitary = $this->solitaryDao->findById($solitaryId);

        if($solitary == null || $solitary->opponentId == null) {
            return null;
        }

        $this->cancelChallenge($solitary->challengeId, $userId, $solitaryId);
        return $solitary;
    }

    public function cancelChallenge($challengeId, $userId, $solarityId){
        $challenge = $this->challengeDao->findById($challengeId);
        $challenge->cancelerId = $userId;
        $this->challengeDao->save($challenge);
        $this->finishSolitaryChallenge($solarityId);
    }

    public function joinSolitaryChallenge($userId, $solitaryId){
        $solitary = $this->solitaryDao->findById($solitaryId);

        if($solitary == null || ($solitary->opponentId != null && $solitary->opponentId != $userId)) {
            return null;
        }

        $solitary = $this->updateSolitaryOpponentId($userId, $solitaryId);
        $this->sendNotificationForStartSolitaryChallenge($solitary->userId, $solitary->id, $userId);
        $this->sendNotificationForStartSolitaryChallenge($userId, $solitary->id, $solitary->userId);
        FRMCHALLENGE_BOL_GeneralService::getInstance()->startChallenge($solitary->challengeId);
        return $solitary;
    }

    public function checkSolitaryChallengePageAccess($userId, $solitaryId){
        if($solitaryId == null){
            return false;
        }

        $solitary = $this->solitaryDao->findById($solitaryId);
        if($solitary == null){
            return false;
        }

        if($solitary->opponentId == null){
            return false;
        }

        if($solitary->opponentId != $userId && $solitary->userId != $userId){
            return false;
        }

        $challenge = $this->challengeDao->findById($solitary->challengeId);

        if($challenge == null){
            return false;
        }

        if($challenge->status == FRMCHALLENGE_BOL_GeneralService::STATUS_REQUEST){
            return false;
        }

        return true;
    }

    public function findSolitaryChallenge($data, $userId){
        $result['error'] = false;
        $result['join'] = false;
        $result['message'] = "";
        $result['solitaryId'] = null;
        $findSolitary = null;

        $categoryId = null;
        if(isset($data['categoryId']) && !empty($data['categoryId'])){
            $categoryId = $data['categoryId'];
        }

        $solitaries = FRMCHALLENGE_BOL_GeneralService::getInstance()->getUserChallengesInfo($userId)['solitary_request_opponent'];
        foreach ($solitaries as $solitary){
            if($solitary['status'] == FRMCHALLENGE_BOL_GeneralService::STATUS_REQUEST) {
                if ($this->checkCategoryIsEqual($categoryId, $solitary['categories_id']) && $solitary['opponent_id'] == $userId) {
                    $findSolitary = $solitary;
                }
            }
        }

        $publicSolitary = $this->getPublicSolitaryChallengesInfo($userId);
        foreach ($publicSolitary as $solitary){
            if($solitary['status'] == FRMCHALLENGE_BOL_GeneralService::STATUS_REQUEST) {
                if ($this->checkCategoryIsEqual($categoryId, $solitary['categories_id']) && $findSolitary == null) {
                    $findSolitary = $solitary;
                }
            }
        }

        if($findSolitary != null){
            $result['join'] = true;
            $result['solitaryId'] = $solitary['id'];
            if($solitary['opponent_id'] == null) {
                $this->updateSolitaryOpponentId($userId, $solitary['id']);
            }
            $this->sendNotificationForStartSolitaryChallenge($solitary['userId'], $solitary['id'], $userId);
            $this->sendNotificationForStartSolitaryChallenge($userId, $solitary['id'], $solitary['userId']);
            FRMCHALLENGE_BOL_GeneralService::getInstance()->startChallenge($solitary['challenge_id']);
            return $result;
        }

        return $result;
    }

    public function checkCategoryIsEqual($posted, $solitaryCategories){
        if($posted == ""){
            $posted = null;
        }

        if($solitaryCategories == ""){
            $solitaryCategories = null;
        }

        $dataCategory = null;
        if(is_int($solitaryCategories) || is_array($solitaryCategories)){
            $solitaryCategories = $this->populateCategoryArray($solitaryCategories);
        }
        if ($solitaryCategories == null && $posted == null) {
            return true;
        } else if ($solitaryCategories != null && $posted != null && in_array($posted, $solitaryCategories)) {
            return true;
        }

        return false;
    }

    public function populateCategoryArray($dataCategory){
        if(is_array($dataCategory)){
            $tempCategory = array();
            foreach ($dataCategory as $cat){
                if(is_int($cat)){
                    $tempCategory[] = $cat;
                }
            }
            $dataCategory = $tempCategory;
            if(sizeof($dataCategory) == 0){
                $dataCategory = null;
            }
        }
        return $dataCategory;
    }

    public function updateSolitaryOpponentId($opponentId, $solitaryId){
        $solitary = $this->solitaryDao->findById($solitaryId);
        $solitary->opponentId = $opponentId;
        $this->solitaryDao->save($solitary);
        return $solitary;
    }

    public function sendNotificationForRequestSolitaryChallenge($userId, $solitaryId, $opponentId){
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $username = BOL_UserService::getInstance()->getDisplayName($opponentId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($opponentId);
        $notificationData = array(
            'string' => array(
                'key' => 'frmchallenge+notification_request_to_join',
                'vars' => array(
                    'url' => OW::getRouter()->urlForRoute('frmchallenge.challenge.join', array('typeId' => FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE,'entityId' => $solitaryId)),
                    'username' => $username,
                    'user_url' => $userUrl
                )
            ),
            'avatar' => $avatars[$userId],
            'content' => '',
            'url' => OW::getRouter()->urlForRoute('frmchallenge.challenge.join', array('typeId' => FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE,'entityId' => $solitaryId)),
            'username' => $username,
            'user_url' => $userUrl
        );
        $entityKey = 'solitary-request';
        $entityId = $solitaryId;
        $this->sendNotification($notificationData, $entityId, $userId, $entityKey);
    }

    public function sendNotificationForAnswerSolitaryChallenge($userId, $solitaryId, $opponentId){
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $username = BOL_UserService::getInstance()->getDisplayName($opponentId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($opponentId);
        $notificationData = array(
            'string' => array(
                'key' => 'frmchallenge+notification_request_to_answer',
                'vars' => array(
                    'url' => OW::getRouter()->urlForRoute('frmchallenge.solitary.challenge', array('solitaryId' => $solitaryId)),
                    'username' => $username,
                    'user_url' => $userUrl
                )
            ),
            'avatar' => $avatars[$userId],
            'content' => '',
            'url' => OW::getRouter()->urlForRoute('frmchallenge.solitary.challenge', array('solitaryId' => $solitaryId))
        );
        $entityKey = 'solitary-answer-request';
        $entityId = $solitaryId;
        $this->sendNotification($notificationData, $entityId, $userId, $entityKey);
    }

    public function sendNotificationForStartSolitaryChallenge($userId, $solitaryId, $opponentId){
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $username = BOL_UserService::getInstance()->getDisplayName($opponentId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($opponentId);
        $notificationData = array(
            'string' => array(
                'key' => 'frmchallenge+notification_request_to_start_challenge',
                'vars' => array(
                    'url' => OW::getRouter()->urlForRoute('frmchallenge.solitary.challenge', array('solitaryId' => $solitaryId)),
                    'username' => $username,
                    'user_url' => $userUrl

                )
            ),
            'avatar' => $avatars[$userId],
            'content' => '',
            'url' => OW::getRouter()->urlForRoute('frmchallenge.solitary.challenge', array('solitaryId' => $solitaryId))
        );
        $entityKey = 'solitary-challenge-start';
        $entityId = $solitaryId;
        $this->sendNotification($notificationData, $entityId, $userId, $entityKey);
    }

    public function sendNotification($notificationData, $entityId, $userId, $entityKey){
        $notificationParams = array(
            'pluginKey' => 'frmchallenge',
            'action' => $entityKey,
            'entityType' => $entityKey,
            'entityId' => $entityId,
            'userId' => $userId,
            'time' => time()
        );

        $event = new OW_Event('notifications.add', $notificationParams, $notificationData);
        OW::getEventManager()->trigger($event);
    }

    public function onCollectNotificationActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'frmchallenge',
            'action' => 'solitary-challenge-start',
            'description' => OW::getLanguage()->text('frmchallenge', 'email_start_challenge_notifications_alerts'),
            'selected' => true,
            'sectionLabel' => OW::getLanguage()->text('frmchallenge', 'email_notification_alerts_label'),
            'sectionIcon' => 'ow_ic_write'
        ));

        $e->add(array(
            'section' => 'frmchallenge',
            'action' => 'solitary-answer-request',
            'description' => OW::getLanguage()->text('frmchallenge', 'email_answer_challenge_notifications_alerts'),
            'selected' => true,
            'sectionLabel' => OW::getLanguage()->text('frmchallenge', 'email_notification_alerts_label'),
            'sectionIcon' => 'ow_ic_write'
        ));

        $e->add(array(
            'section' => 'frmchallenge',
            'action' => 'solitary-request',
            'description' => OW::getLanguage()->text('frmchallenge', 'email_request_challenge_notifications_alerts'),
            'selected' => true,
            'sectionLabel' => OW::getLanguage()->text('frmchallenge', 'email_notification_alerts_label'),
            'sectionIcon' => 'ow_ic_write'
        ));
    }
}
