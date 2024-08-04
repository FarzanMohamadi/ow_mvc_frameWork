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
class FRMCHALLENGE_BOL_GeneralService
{
    private static $classInstance;
    const SOLITARY_TYPE = 1;
    const GROUPS_TYPE = 2;
    const UNIVERSAL_TYPE = 3;

    const STATUS_REQUEST = 1;
    const STATUS_PENDING = 2;
    const STATUS_FINISH = 3;

    private $challengeDao;
    private $categoryDao;
    private $universalDao;
    private $solitaryDao;
    private $questionDao;
    private $bookletDao;
    private $userAnswerDao;
    private $answerDao;
    private $userDao;

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
        $this->universalDao = FRMCHALLENGE_BOL_UniversalDao::getInstance();
        $this->solitaryDao = FRMCHALLENGE_BOL_SolitaryDao::getInstance();
        $this->questionDao = FRMCHALLENGE_BOL_QuestionDao::getInstance();
        $this->bookletDao = FRMCHALLENGE_BOL_BookletDao::getInstance();
        $this->userDao = FRMCHALLENGE_BOL_UserDao::getInstance();
        $this->userAnswerDao = FRMCHALLENGE_BOL_UserAnswerDao::getInstance();
        $this->answerDao = FRMCHALLENGE_BOL_AnswerDao::getInstance();
    }

    /***
     * @param $type
     * @throws Redirect404Exception
     */
    public function checkChallengeTypeValid($type){
        if($type != FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE &&
            $type != FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE &&
            $type != FRMCHALLENGE_BOL_GeneralService::GROUPS_TYPE){
            throw new Redirect404Exception();
        }
    }

    public function getChallengeCreationForm(){
        $language = OW::getLanguage();
        $form = new Form('create_challenge');
        $form->setAction(OW::getRouter()->urlForRoute('frmchallenge.add.challenge'));
        $form->setId('create_challenge');

        $fieldLabel = new TextField('title');
        $fieldLabel->setLabel(OW::getLanguage()->text('frmchallenge', 'title'));
        $fieldLabel->setHasInvitation(false);
        $form->addElement($fieldLabel);

        $fieldLabel = new TextField('description');
        $fieldLabel->setLabel(OW::getLanguage()->text('frmchallenge', 'description'));
        $fieldLabel->setHasInvitation(false);
        $form->addElement($fieldLabel);

        $fieldLabel = new TextField('prize');
        $fieldLabel->setLabel(OW::getLanguage()->text('frmchallenge', 'prize'));
        $fieldLabel->setHasInvitation(false);
        $form->addElement($fieldLabel);

        $fieldLabel = new TextField('sponsor');
        $fieldLabel->setLabel(OW::getLanguage()->text('frmchallenge', 'sponsor'));
        $fieldLabel->setHasInvitation(false);
        $form->addElement($fieldLabel);

        $fieldLabel = new TextField('min_point');
        $fieldLabel->setLabel(OW::getLanguage()->text('frmchallenge', 'min_point'));
        $fieldLabel->addValidator(new IntValidator());
        $fieldLabel->setHasInvitation(false);
        $form->addElement($fieldLabel);

        $fieldLabel = new TextField('questions_number');
        $fieldLabel->setLabel(OW::getLanguage()->text('frmchallenge', 'questions_number'));
        $fieldLabel->addValidator(new IntValidator());
        $fieldLabel->setValue(10);
        $fieldLabel->setHasInvitation(false);
        $form->addElement($fieldLabel);

        $currentYear = date('Y', time());
        if(OW::getConfig()->getValue('frmjalali', 'dateLocale')==1){
            $currentYear=$currentYear-1;
        }
        $startDate = new DateField('start_date');
        $startDate->setLabel(OW::getLanguage()->text('frmchallenge', 'start_time'));
        $startDate->setMinYear($currentYear);
        $startDate->setMaxYear($currentYear + 5);
        $form->addElement($startDate);

        $fieldLabel = new TextField('end_time');
        $fieldLabel->setLabel(OW::getLanguage()->text('frmchallenge', 'end_time'));
        $fieldLabel->addValidator(new IntValidator());
        $fieldLabel->setHasInvitation(false);
        $form->addElement($fieldLabel);

        $fieldLabel = new HiddenField('win_point');
        $fieldLabel->setValue(0);
        $form->addElement($fieldLabel);

        $fieldLabel = new TextField('win_num');
        $fieldLabel->setLabel(OW::getLanguage()->text('frmchallenge', 'win_num'));
        $fieldLabel->addValidator(new IntValidator());
        $fieldLabel->setHasInvitation(false);
        $fieldLabel->setValue(10);
        $form->addElement($fieldLabel);

        if($this->canUserCreateChallenge()){
            $typeField = new RadioField('challenge_type');
            $options = array();
            if($this->isSolitaryChallengeEnable()){
                $options[FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE] = OW::getLanguage()->text('frmchallenge', 'solitary_challenge_label');
                $typeField->setValue(FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE);

            }
            if(FRMCHALLENGE_BOL_UniversalService::getInstance()->hasAuthorizeToCreateUniversal(false)) {
                $options[FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE] = OW::getLanguage()->text('frmchallenge', 'public_challenge_tab_label');
                $typeField->setValue(FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE);
            }
            $typeField->addOptions($options);
            $typeField->setLabel($language->text('frmchallenge', 'challenge_type'));
            $form->addElement($typeField);
        }else{
            $typeField = new TextField('challenge_type');
            $typeField->setLabel($language->text('frmchallenge', 'challenge_type'));
            $form->addElement($typeField);
        }

        $fieldLabel = new TextField('opponent_username');
        $fieldLabel->setLabel(OW::getLanguage()->text('frmchallenge', 'find_opponent_text'));
        $fieldLabel->setHasInvitation(false);
        $form->addElement($fieldLabel);

        $categoryField = new Selectbox('categoryId');
        $categoryField->setHasInvitation(false);
        $categoryField->setLabel($language->text('frmchallenge', 'category'));
        $options = array();
        $options[''] = OW::getLanguage()->text('frmchallenge', 'all_category');
        $categories = $this->getAllCategories(OW::getConfig()->getValue('frmchallenge', 'solitary_question_count'));
        foreach ($categories as $category){
            $options[$category->id] = $category->title;
        }
        $categoryField->setOptions($options);
        $form->addElement($categoryField);

        $categoryField = new Selectbox('universalCategoryId');
        $categoryField->setHasInvitation(false);
        $categoryField->setLabel($language->text('frmchallenge', 'category'));
        $options = array();
        $options[''] = OW::getLanguage()->text('frmchallenge', 'all_category');
        $categories = $this->getAllCategories(OW::getConfig()->getValue('frmchallenge', 'universal_question_count'));
        foreach ($categories as $category){
            $options[$category->id] = $category->title;
        }
        $categoryField->setOptions($options);
        $form->addElement($categoryField);

        return $form;
    }

    public function addChallenge($data){
        $result = array();
        $result['error'] = false;
        $result['message'] = "";
        $result['challenge'] = null;
        $solitaryService = FRMCHALLENGE_BOL_SolitaryService::getInstance();

        if($data['type'] == FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE){
            if((!isset($data['title']) || empty($data['title']))){
                $result['error'] = true;
                $result['message'] = OW::getLanguage()->text('frmchallenge', 'empty_title');
            }

            if((!isset($data['min_point']) || $data['min_point'])<0){
                $result['error'] = true;
                $result['message'] = OW::getLanguage()->text('frmchallenge', 'empty_min_point');
            }

            if((!isset($data['win_point']) || $data['win_point']<0)){
                $result['error'] = true;
                $result['message'] = OW::getLanguage()->text('frmchallenge', 'empty_win_point');
            }
        }

        if(!$result['error']){
            $challenge = new FRMCHALLENGE_BOL_Challenge();
            $challenge->createDate = time();
            $challenge->status = FRMCHALLENGE_BOL_GeneralService::STATUS_REQUEST;
            if($data['type'] == FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE){
                $startStamp = time();
                $form = FRMCHALLENGE_BOL_GeneralService::getInstance()->getChallengeCreationForm();
                $form->isValid($_POST);
                $formDataValue = $form->getValues();
                if(isset($formDataValue['start_date']) && !empty($formDataValue['start_date'])){
                    $dateArray = explode('/', $formDataValue['start_date']);
                    $startStamp = mktime(0, 0, 0, (int) $dateArray[1], (int) $dateArray[2], (int) $dateArray[0]);
                }
                if(isset($data['end_time'])){
                    $day = $data['end_time'];
                    $challenge->finishDate = $day * 24 * 60 * 60 + $startStamp;
                }else {
                    $hour = OW::getConfig()->getValue('frmchallenge', 'universal_answer_time');
                    $hour = (int) $hour;
                    $challenge->finishDate = $hour * 60 * 60 + time();
                }
                $challenge->status = FRMCHALLENGE_BOL_GeneralService::STATUS_PENDING;
            }else{
                $challenge->finishDate = time() + OW::getConfig()->getValue('frmchallenge', 'solitary_answer_time');
            }
            $challenge->type = $data['type'];
            if($data['type'] == self::SOLITARY_TYPE || $data['type'] == self::GROUPS_TYPE) {
                $challenge->minPoint = 0;
                $challenge->winPoint = $solitaryService->getWinSolitaryPoint();
                $challenge->losePoint = $solitaryService->getLoseSolitaryPoint();
                $challenge->equalPoint = $solitaryService->getEqualSolitaryPoint();
            }else{
                $challenge->minPoint = UTIL_HtmlTag::stripTagsAndJs($data['min_point']);
                $challenge->winPoint = UTIL_HtmlTag::stripTagsAndJs($data['win_point']);
                $challenge->losePoint = 0;
                $challenge->equalPoint = 0;
            }

            if(isset($data['title'])) {
                $challenge->title = UTIL_HtmlTag::stripTagsAndJs($data['title']);
            }

            if(isset($data['prize'])) {
                $challenge->prize = UTIL_HtmlTag::stripTagsAndJs($data['prize']);
            }

            if(isset($data['sponsor'])) {
                $challenge->sponsor = UTIL_HtmlTag::stripTagsAndJs($data['sponsor']);
            }

            if(isset($data['categoryId']) && !empty($data['categoryId'])){
                $challenge->categories = UTIL_HtmlTag::stripTagsAndJs($data['categoryId']);
            }

            if(isset($data['description'])){
                $challenge->description = UTIL_HtmlTag::stripTagsAndJs($data['description']);
            }

            $this->challengeDao->save($challenge);

            $result['challenge'] = $challenge;
        }

        return $result;
    }

    public function addStylesAdnScripts(){
        $cssUrl = OW::getPluginManager()->getPlugin('frmchallenge')->getStaticCssUrl() . "frmchallenge.css";
        OW::getDocument()->addStyleSheet($cssUrl);

        $jsUrl = OW::getPluginManager()->getPlugin('frmchallenge')->getStaticJsUrl() . "frmchallenge.js";
        OW::getDocument()->addScript($jsUrl);

        $jsUrl = OW::getPluginManager()->getPlugin('frmchallenge')->getStaticJsUrl() . "jquery.steps.js";
        OW::getDocument()->addScript($jsUrl);

        $js = "var chaluserLoadUsernamesUrl='". OW::getRouter()->urlForRoute('frmchallenge.load_usernames', array('username'=>''))."';";
        $js = $js.";var chaluserMaxCount=5;";
        OW::getDocument()->addScriptDeclarationBeforeIncludes($js);
        $jsUrl = OW::getPluginManager()->getPlugin('frmchallenge')->getStaticJsUrl() . "suggest.js";
        OW::getDocument()->addScript($jsUrl);

        OW::getLanguage()->addKeyForJs('frmchallenge', 'next_label');
        OW::getLanguage()->addKeyForJs('frmchallenge', 'previous_label');
        OW::getLanguage()->addKeyForJs('frmchallenge', 'finish_label');
    }

    public function getChallengeMaxPoint($challengeId){
        $booklets = $this->findBooklets($challengeId);
        $point = 0;
        foreach ($booklets as $booklet){
            $question = $this->questionDao->findById($booklet->questionId);
            $point += $question->point;
        }

        return $point;
    }

    public function getChallengeInfo($entityId, $type, $userId){
        return $this->populateChallengeInfo($entityId, $userId, $type);
    }

    public function populateChallengeInfo($entityId, $userId, $type, $challenge = null){
        $challengeId = null;
        if($challenge == null) {
            if($type == self::SOLITARY_TYPE){
                $solitary = $this->solitaryDao->findById($entityId);
                $challengeId = $solitary->challengeId;
            }else if($type == self::UNIVERSAL_TYPE) {
                $universal = $this->universalDao->findById($entityId);
                $challengeId = $universal->challengeId;
            }
            $challenge = $this->challengeDao->findById($challengeId);
        }else{
            $challengeId = $challenge->id;
        }

        $data['title'] = $challenge->title;
        $data['status'] = $challenge->status;
        $data['win_point'] = $challenge->winPoint;
        $data['lose_point'] = $challenge->losePoint;
        $data['equal_point'] = $challenge->equalPoint;
        $data['min_point'] = $challenge->minPoint;
        $data['description'] = "";
        if($challenge->description != null){
            $data['description'] = $challenge->description;
        }
        $data['challenge_id'] = $challenge->id;
        $data['cancelerId'] = $challenge->cancelerId;
        $data['wait_for_answer'] = false;

        $data['join_label'] = "";
        $data['join_url'] = "";
        $data['cancel_label'] = OW::getLanguage()->text('frmchallenge', 'cancel_label');
        $data['cancel_url'] = "";

        $data['categories_string'] = FRMCHALLENGE_BOL_SolitaryService::getInstance()->getAllCategoryLabel();
        $data['categories_id'] = FRMCHALLENGE_BOL_SolitaryService::getInstance()->getAllCategoryLabel();
        $data['max_point'] = $this->getChallengeMaxPoint($challengeId);

        if($challenge->categories != null){
            $categoryId = $challenge->categories;
            $category = $this->categoryDao->findById($categoryId);
            if($category != null) {
                $data['categories_string'] = $category->title;
                $data['categories_id'] = $category->id;
            }
        }

        if($challenge->status == FRMCHALLENGE_BOL_GeneralService::STATUS_FINISH){
            $data['finish_status'] = true;
        }

        if($type == self::SOLITARY_TYPE){
            $solitary = $this->solitaryDao->findById($entityId);

            if($challenge->status != FRMCHALLENGE_BOL_GeneralService::STATUS_REQUEST) {
                if ($solitary->userId == $userId) {
                    $data['userPoint'] = $this->findPointOfUserInChallenge($solitary->id, self::SOLITARY_TYPE, $userId, $challengeId);
                    $data['opponentPoint'] = $this->findPointOfUserInChallenge($solitary->id, self::SOLITARY_TYPE, $solitary->opponentId, $challengeId);
                } else {
                    $data['userPoint'] = $this->findPointOfUserInChallenge($solitary->id, self::SOLITARY_TYPE, $userId, $challengeId);
                    $data['opponentPoint'] = $this->findPointOfUserInChallenge($solitary->id, self::SOLITARY_TYPE, $solitary->userId, $challengeId);
                }
            }

            $data['opponent_name'] = OW::getLanguage()->text('frmchallenge', 'unknown_opponent');
            $data['opponent_id'] = null;
            $data['opponent_url'] = null;
            if($solitary->opponentId != null) {
                if($solitary->opponentId == OW::getUser()->getId()){
                    $user = BOL_UserService::getInstance()->findUserById($solitary->userId);
                }else{
                    $user = BOL_UserService::getInstance()->findUserById($solitary->opponentId);
                }
                if($user != null){
                    $data['opponent_name'] = BOL_UserService::getInstance()->getDisplayName($user->getId());
                    $data['opponent_url'] = BOL_UserService::getInstance()->getUserUrl($user->getId());
                    $data['opponent_id'] = $user->getId();
                }
            }

            $resultAnswer = $this->isUserMustAnswer($solitary->id, $type, $userId);
            if ($challenge->status == FRMCHALLENGE_BOL_GeneralService::STATUS_PENDING && !$resultAnswer['userMustAnswer']) {
                $data['wait_for_answer'] = true;
            }

            if($challenge->status == FRMCHALLENGE_BOL_GeneralService::STATUS_REQUEST){
                if($solitary->opponentId == $userId || $solitary->userId != $userId) {
                    $data['join_label'] = OW::getLanguage()->text('frmchallenge', 'challenge_button_join');
                    $data['join_url'] = OW::getRouter()->urlForRoute('frmchallenge.challenge.join', array('typeId' => FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE,'entityId' => $solitary->id));;
                }else if($solitary->opponentId == null || $solitary->userId == $userId) {
                    if($solitary->opponentId == null){
                        $data['join_label'] = OW::getLanguage()->text('frmchallenge', 'challenge_button_wait');
                    }else{
                        $data['join_label'] = OW::getLanguage()->text('frmchallenge', 'challenge_button_wait_opponent');
                    }
                }
            }else if($challenge->status == FRMCHALLENGE_BOL_GeneralService::STATUS_PENDING){
                $data['finish_date'] = UTIL_DateTime::formatSimpleDate($challenge->finishDate);
                $data['join_label'] = OW::getLanguage()->text('frmchallenge', 'challenge_button_continue');
                $data['join_url'] = OW::getRouter()->urlForRoute('frmchallenge.solitary.challenge', array('solitaryId' => $solitary->id));
                $data['cancel_url'] = OW::getRouter()->urlForRoute('frmchallenge.solitary.cancel', array('solitaryId' => $solitary->id));;
            }else if($challenge->status == FRMCHALLENGE_BOL_GeneralService::STATUS_FINISH){
                $data['join_label'] = OW::getLanguage()->text('frmchallenge', 'challenge_button_view_result');
                $data['join_url'] = OW::getRouter()->urlForRoute('frmchallenge.solitary.challenge', array('solitaryId' => $solitary->id));
            }

            $data['id'] = $solitary->id;
            $data['userId'] = $solitary->userId;
        }else if($type == self::UNIVERSAL_TYPE){
            $universal = $this->universalDao->findById($entityId);
            $data['finish_date'] = UTIL_DateTime::formatSimpleDate($challenge->finishDate);
            $data['userId'] = $universal->userId;
            $data['id'] = $universal->id;
            $data['userId'] = $universal->userId;
            $data['start_time'] = UTIL_DateTime::formatSimpleDate($universal->startTime);
            $data['userPoint'] = $this->findPointOfUserInChallenge($universal->id, self::UNIVERSAL_TYPE, $userId, $challengeId);
            if(time() < $universal->startTime){
                $data['not_start'] = true;
            }
            $winnerInfo = FRMCHALLENGE_BOL_UniversalService::getInstance()->findUniversalWinner($universal->id);
            if($winnerInfo['userId'] != -1) {
                $data['winner_username'] = BOL_UserService::getInstance()->getDisplayName($winnerInfo['userId']);
                $data['winner_url'] = BOL_UserService::getInstance()->getUserUrl($winnerInfo['userId']);
                $data['winner_point'] = $winnerInfo['point'];
                if($challenge->status == self::STATUS_FINISH){
                    $data['winner_label'] = OW::getLanguage()->text('frmchallenge', 'winner');
                }else{
                    $data['winner_label'] = OW::getLanguage()->text('frmchallenge', 'current_winner');
                }
            }

            if($challenge->status == FRMCHALLENGE_BOL_GeneralService::STATUS_PENDING){
                $involve = $this->checkUserInvolve($universal->id, self::UNIVERSAL_TYPE, $userId, $universal->challengeId);
                if($involve){
                    $data['join_label'] = OW::getLanguage()->text('frmchallenge', 'challenge_button_continue');
                    $result = $this->isUserMustAnswer($universal->id, self::UNIVERSAL_TYPE, $userId);
                    if(isset($result['userMustAnswer']) && !$result['userMustAnswer']){
                        $data['join_label'] = OW::getLanguage()->text('frmchallenge', 'challenge_button_view_result');
                    }
                }else{
                    $data['join_label'] = OW::getLanguage()->text('frmchallenge', 'challenge_button_join');
                }
                $data['join_url'] = OW::getRouter()->urlForRoute('frmchallenge.universal.challenge', array('universalId' => $universal->id));
            }else if($challenge->status == FRMCHALLENGE_BOL_GeneralService::STATUS_FINISH){
                $data['join_label'] = OW::getLanguage()->text('frmchallenge', 'challenge_button_view_result');
                $data['join_url'] = OW::getRouter()->urlForRoute('frmchallenge.universal.challenge', array('universalId' => $universal->id));
            }

            $data['users_point'] = FRMCHALLENGE_BOL_UniversalService::getInstance()->findUniversalUserPoints($universal->id, $universal->winNum);
        }

        return $data;
    }

    public function findUsersWithHighestPoint(){
        return array();
    }

    /***
     * @param $challengeId
     * @return array
     */
    public function findBooklets($challengeId){
        return $this->bookletDao->findBooklets($challengeId);
    }

    public function allQuestionsAnswered($challengeId){
        $booklets = $this->findBooklets($challengeId);
        foreach ($booklets as $booklet){
            if ($booklet->userIdSeen == 0 || $booklet->opponentIdSeen == 0) {
                return false;
            }
        }

        return true;
    }

    public function getUserChallengesInfo($userId){
        $result = array();
        $result['solitary_request_finish'] = array();
        $result['solitary_request_self'] = array();
        $result['solitary_request_opponent'] = array();

        $result['universal_request_finish'] = array();
        $result['universal_request_self'] = array();
        $result['universal_request_public'] = array();

        $solitaries = $this->solitaryDao->getSolitaryChallenges($userId);
        foreach ($solitaries as $solitary){
            $challenge = $this->challengeDao->findById($solitary->challengeId);
            $data = $this->populateChallengeInfo($solitary->id, $userId, self::SOLITARY_TYPE, $challenge);
            if($challenge->status == self::STATUS_REQUEST){
                if($solitary->opponentId == null || $solitary->userId == $userId) {
                    $result['solitary_request_self'][] = $data;
                }else if($solitary->opponentId == $userId) {
                    $result['solitary_request_opponent'][] = $data;
                }
            }else if($challenge->status == self::STATUS_PENDING){
                $result['solitary_request_self'][] = $data;
            }else if($challenge->status == self::STATUS_FINISH){
                $result['solitary_request_finish'][] = $data;
            }
        }

        $universalChallenges = $this->universalDao->getUniversalChallenges();
        foreach ($universalChallenges as $universal){
            $canInvolve = $this->checkUserPointToInvolve($universal->id, self::UNIVERSAL_TYPE, $userId);
            if($canInvolve) {
                $challenge = $this->challengeDao->findById($universal->challengeId);
                $data = $this->populateChallengeInfo($universal->id, $userId, self::UNIVERSAL_TYPE, $challenge);
                if ($challenge->status == self::STATUS_PENDING) {
                    $userInvolve = $this->checkUserInvolve($universal->id, self::UNIVERSAL_TYPE, $userId, $challenge->id);
                    if ($userInvolve) {
                        $result['universal_request_self'][] = $data;
                    } else {
                        $result['universal_request_public'][] = $data;
                    }
                } else if ($challenge->status == self::STATUS_FINISH) {
                    $result['universal_request_finish'][] = $data;
                }
            }
        }

        return $result;
    }

    public function processExpiredFinishDateChallenge($entityId, $type, $redirect = false){
        $challengeId = null;
        if($type == self::SOLITARY_TYPE){
            $solitary = $this->solitaryDao->findById($entityId);
            $challengeId = $solitary->challengeId;
        }else if($type == self::UNIVERSAL_TYPE){
            $universal = $this->universalDao->findById($entityId);
            $challengeId = $universal->challengeId;
        }
        $challenge = $this->challengeDao->findById($challengeId);

        if($challenge->status != self::STATUS_FINISH && $challenge->finishDate!= null && $challenge->finishDate <= time()){
            if($challenge->type == self::SOLITARY_TYPE){
                $solitary = $this->solitaryDao->findById($entityId);
                FRMCHALLENGE_BOL_SolitaryService::getInstance()->finishSolitaryChallenge($solitary->id);
                if($redirect) {
                    OW::getFeedback()->info(OW::getLanguage()->text('frmchallenge', 'challenge_finished'));
                    OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmchallenge.solitary.challenge', array('solitaryId' => $solitary->id)));
                }
            }else if($challenge->type == self::UNIVERSAL_TYPE){
                $universal = $this->universalDao->findById($entityId);
                FRMCHALLENGE_BOL_UniversalService::getInstance()->finishUniversalChallenge($universal->id);
                if($redirect) {
                    OW::getFeedback()->info(OW::getLanguage()->text('frmchallenge', 'challenge_finished'));
                    OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmchallenge.universal.challenge', array('universalId' => $universal->id)));
                }
            }
            return true;
        }

        return false;
    }

    public function findQuestionAnswer($questionId){
        return $this->answerDao->findQuestionAnswer($questionId);
    }

    public function checkAnswerExist($questionId, $answerId){
        $answers = $this->findQuestionAnswer($questionId);
        foreach ($answers as $answer){
            if($answer->id == $answerId){
                return true;
            }
        }

        return false;
    }

    public function checkChallengeUserCanAnswer($userId, $challengeId, $questionId){
        $answerBefore = $this->findUserAnswer($questionId, $userId, $challengeId);
        if($answerBefore == null){
            return true;
        }

        $challenge = $this->challengeDao->findById($challengeId);
        if($challenge->cancelerId != null || $challenge->status != self::STATUS_PENDING){
            return false;
        }

        return false;
    }

    public function findUserAnswer($questionId, $userId, $challengeId){
        return $this->userAnswerDao->findUserAnswer($questionId, $userId, $challengeId);
    }

    /***
     * @param $entityId
     * @param $type
     * @param $userId
     * @return mixed
     */
    public function getQuestionInfo($entityId, $type, $userId){
        $solitaryService = FRMCHALLENGE_BOL_SolitaryService::getInstance();
        $challengeId = null;
        $solitary = null;
        $universal = null;

        if($type == self::SOLITARY_TYPE){
            $solitary = $this->solitaryDao->findById($entityId);
            $challengeId = $solitary->challengeId;
        }else if($type == self::UNIVERSAL_TYPE){
            $universal = $this->universalDao->findById($entityId);
            $challengeId = $universal->challengeId;
        }

        $challenge = $this->challengeDao->findById($challengeId);

        $data = array();
        $data['finish'] = false;
        $data['cancel'] = false;

        if($challenge->cancelerId != null){
            $data['cancel'] = true;
        }

        if($challenge->finishDate != null){
            $data['finish_date'] = UTIL_DateTime::formatSimpleDate($challenge->finishDate);
        }

        $result = $this->isUserMustAnswer($entityId, $type, $userId);
        $booklet = $result['booklet'];
        $userMustAnswer = $result['userMustAnswer'];
        if($challenge->status == self::STATUS_FINISH){
            $data['finish_status'] = true;
            $data['finish'] = true;
        }else if(!$data['cancel'] && $booklet != null) {
            $question = $this->questionDao->findById($booklet->questionId);
            $data['title'] = $question->title;
            $answers = $this->findQuestionAnswer($booklet->questionId);
            $data['form'] = $this->getAnswerForm($entityId, $question->id, $answers, $type);
            $data['questionId'] = $question->id;

            if($type == self::SOLITARY_TYPE) {
                $value = null;
                foreach ($answers as $answer) {
                    if ($answer->correct == 1 && !$userMustAnswer) {
                        $value = $answer->title;
                    }
                }

                if (!$userMustAnswer) {
                    $data['value'] = $value;
                }
            }
        }else if(!$data['cancel']){
            if($type == self::SOLITARY_TYPE) {
                $solitaryService->finishSolitaryChallenge($entityId);
            }
            $data['finish'] = true;
        }

        if($type == self::SOLITARY_TYPE){
            if($solitary->userId == $userId){
                $data['userPoint'] = $this->findPointOfUserInChallenge($solitary->id, self::SOLITARY_TYPE, $userId, $challengeId);
                $data['opponentPoint'] = $this->findPointOfUserInChallenge($solitary->id, self::SOLITARY_TYPE, $solitary->opponentId, $challengeId);
            }else{
                $data['userPoint'] = $this->findPointOfUserInChallenge($solitary->id, self::SOLITARY_TYPE, $userId, $challengeId);
                $data['opponentPoint'] = $this->findPointOfUserInChallenge($solitary->id, self::SOLITARY_TYPE, $solitary->userId, $challengeId);
            }

            $canCancel = $solitaryService->userCanCancelSolitaryChallenge($userId, $solitary->id);
            if($canCancel){
                $data['cancel_label'] = OW::getLanguage()->text('frmchallenge', 'cancel_label');
                $data['cancel_url'] = OW::getRouter()->urlForRoute('frmchallenge.solitary.cancel', array('solitaryId' => $solitary->id));;
            }
        }else if($type == self::UNIVERSAL_TYPE){
            $data['start_date'] = UTIL_DateTime::formatSimpleDate($universal->startTime);
            $data['userPoint'] = $this->findPointOfUserInChallenge($universal->id, self::UNIVERSAL_TYPE, $userId, $challengeId);
            if(time() < $universal->startTime){
                $data['not_start'] = true;
            }
        }

        return $data;
    }

    /***
     * @param $entityId
     * @param $questionId
     * @param $answers
     * @param $type
     * @return Form
     */
    public function getAnswerForm($entityId, $questionId, $answers, $type){
        $language = OW::getLanguage();
        $form = new Form('answers_form');
        $actionUrl = OW::getRouter()->urlForRoute('frmchallenge.challenge.answer', array('typeId' => $type,'entityId' => $entityId, 'questionId' => $questionId));
        $form->setAction($actionUrl);

        $typeField = new RadioField('answerId');
        $options = array();

        foreach ($answers as $answer){
            $options[$answer->id] = $answer->title;
        }

        $typeField->addOptions($options);
        $typeField->setLabel($language->text('frmchallenge', 'answers'));
        $form->addElement($typeField);

        $submit = new Submit('submit');
        $submit->setValue($language->text('frmchallenge', 'answer'));
        $form->addElement($submit);

        return $form;
    }

    public function isUserMustAnswer($entityId, $type, $userId){
        $challengeId = null;
        if($type == self::SOLITARY_TYPE){
            $solitary = $this->solitaryDao->findById($entityId);
            $challengeId = $solitary->challengeId;
        }else if($type == self::UNIVERSAL_TYPE){
            $universal = $this->universalDao->findById($entityId);
            $challengeId = $universal->challengeId;
        }
        $challenge = $this->challengeDao->findById($challengeId);

        $booklet = $this->findCurrentBookletQuestion($challenge->id, $type, $userId);
        $userMustAnswer = $this->isBookletNeedAnswer($booklet, $type, $userId, $entityId);
        return array('userMustAnswer' => $userMustAnswer, 'booklet' => $booklet);
    }

    public function isBookletNeedAnswer($booklet, $type, $userId, $entityId){
        if($booklet == null){
            return false;
        }
        $userMustAnswer = false;
        if($type == self::SOLITARY_TYPE){
            $solitary = $this->solitaryDao->findById($entityId);
            if ($solitary->userId == $userId && $booklet->userIdSeen == 0) {
                $userMustAnswer = true;
            } else if ($solitary->opponentId == $userId && $booklet->opponentIdSeen == 0) {
                $userMustAnswer = true;
            }
        }else if($type == self::UNIVERSAL_TYPE){
            $universal = $this->universalDao->findById($entityId);
            $challengeId = $universal->challengeId;
            $answer = $this->userAnswerDao->findUserAnswer($booklet->questionId, $userId, $challengeId);
            if($answer == null){
                $userMustAnswer = true;
            }
        }

        return $userMustAnswer;
    }

    /***
     * @param $challengeId
     * @param $type
     * @param $userId
     * @return FRMCHALLENGE_BOL_Booklet
     */
    public function findCurrentBookletQuestion($challengeId, $type, $userId){
        $booklets = $this->findBooklets($challengeId);
        $currentBooklet = null;
        foreach ($booklets as $booklet) {
            if ($currentBooklet == null) {
                if ($type == self::SOLITARY_TYPE) {
                    if ($booklet->userIdSeen == 0 || $booklet->opponentIdSeen == 0) {
                        $currentBooklet = $booklet;
                    }
                } else if ($type == self::UNIVERSAL_TYPE) {
                    $answer = $this->userAnswerDao->findUserAnswer($booklet->questionId, $userId, $challengeId);
                    if($answer == null){
                        $currentBooklet = $booklet;
                    }
                }
            }
        }

        return $currentBooklet;
    }

    public function startChallenge($challengeId){
        $challenge = $this->challengeDao->findById($challengeId);
        $count = OW::getConfig()->getValue('frmchallenge', 'solitary_question_count');
        if($challenge != null){
            if($challenge->type == self::UNIVERSAL_TYPE){
                $universalChallenge = $this->universalDao->findByChallengeId($challengeId);
                if ($universalChallenge == null) {
                    $count = OW::getConfig()->getValue('frmchallenge', 'universal_question_count');
                }else {
                    $count = (int) $universalChallenge->questionsNumber;
                }
            }
        }
        $questions = $this->getRandomQuestionsByCategory($count, $challenge->categories);

        $questionsId = array();
        foreach ($questions as $question){
            $questionsId[] = $question->id;
        }

        $this->addBooklets($questionsId, $challengeId);

        $challenge->status = self::STATUS_PENDING;
        $this->challengeDao->save($challenge);
    }

    public function addBooklet($questionId, $challengeId){
        return $this->bookletDao->addBooklet($questionId, $challengeId);
    }

    public function addBooklets($questionsId, $challengeId){
        return $this->bookletDao->addBooklets($questionsId, $challengeId);
    }

    public function getRandomQuestionsByCategory($count = 5, $categoryId = null){
        return $this->questionDao->getRandomQuestionsByCategory($count, $categoryId);
    }

    /***
     * @param $count
     * @return array
     */
    public function getAllCategories($count = 5){
        $categories = $this->categoryDao->getAllCategories();
        $usableCategories = array();
        foreach ($categories as $category){
            $questions = $this->getRandomQuestionsByCategory($count, $category->id);
            if(sizeof($questions) >= $count){
                $usableCategories[] = $category;
            }
        }
        return $usableCategories;
    }

    public function findPointOfUserInChallenge($entityId, $type, $userId, $challengeId){
        $booklets = $this->findBooklets($challengeId);
        $point = 0;

        foreach ($booklets as $booklet){
            $answerBefore = $this->isBookletNeedAnswer($booklet, $type, $userId, $entityId);
            if(!$answerBefore){
                $userAnswer = $this->findUserAnswer($booklet->questionId, $userId, $challengeId);
                if($userAnswer != null) {
                    $userAnswerId = $userAnswer->answerId;
                    $correctAnswerId = $this->findCorrectAnswerOfQuestion($booklet->questionId);
                    if ($correctAnswerId == $userAnswerId) {
                        $question = $this->questionDao->findById($booklet->questionId);
                        $point += $question->point;
                    }
                }
            }
        }

        return $point;
    }

    public function checkUserInvolve($entityId, $type, $userId, $challengeId){
        $booklets = $this->findBooklets($challengeId);

        foreach ($booklets as $booklet){
            $answerBefore = $this->isBookletNeedAnswer($booklet, $type, $userId, $entityId);
            if(!$answerBefore){
                return true;
            }
        }

        return false;
    }

    public function findCorrectAnswerOfQuestion($questionId){
        $answers = $this->findQuestionAnswer($questionId);
        foreach ($answers as $answer){
            if($answer->correct == 1){
                return $answer->id;
            }
        }

        return null;
    }

    public function addUserPoint($userId, $point){
        return $this->userDao->addUserPoint($userId, $point);
    }

    public function processUserAnswerPoint($questionId, $userId, $answerId){
        $correctAnswerId = $this->findCorrectAnswerOfQuestion($questionId);
        if($correctAnswerId == $answerId){
            $question = $this->questionDao->findById($questionId);
            $this->addUserPoint($userId, $question->point);
        }
    }

    public function addUserAnswer($questionId, $userId, $challengeId, $answerId){
        if($this->checkAnswerExist($questionId, $answerId)) {
            $this->processUserAnswerPoint($questionId, $userId, $answerId);
            return $this->userAnswerDao->addUserAnswer($questionId, $userId, $challengeId, $answerId);
        }

        return null;
    }

    public function processAddWrongUserAnswer($questionId, $userId, $entityId, $type){
        $challengeId = null;
        if($type == self::SOLITARY_TYPE){
            $solitary = $this->solitaryDao->findById($entityId);
            $challengeId = $solitary->challengeId;
        }else if($type == self::UNIVERSAL_TYPE){
            $universal = $this->universalDao->findById($entityId);
            $challengeId = $universal->challengeId;
        }

        if(self::SOLITARY_TYPE == $type) {
            $this->updateFinishTimeChallenge($challengeId);
            $solitaryService = FRMCHALLENGE_BOL_SolitaryService::getInstance();
            $solitary = $this->solitaryDao->findById($entityId);
            if ($solitary == null) {
                return null;
            }

            $isOpponent = false;
            if ($solitary->opponentId == $userId) {
                $isOpponent = true;
            }
            $solitaryService->updateSeenBooklet($questionId, $solitary->challengeId, $isOpponent);
            if ($isOpponent) {
                $solitaryService->sendNotificationForAnswerSolitaryChallenge($solitary->userId, $solitary->id, $solitary->opponentId);
            } else {
                $solitaryService->sendNotificationForAnswerSolitaryChallenge($solitary->opponentId, $solitary->id, $solitary->userId);
            }
        }

        $answers = $this->findQuestionAnswer($questionId);
        $answerFound = null;
        foreach ($answers as $answer){
            if($answer->correct == false){
                $answerFound = $answer;
            }
        }

        if($answerFound == null){
            foreach ($answers as $answer){
                $answerFound = $answer;
            }
        }
        return $this->addUserAnswer($questionId, $userId, $challengeId, $answerFound->id);
    }

    public function processAddUserAnswer($questionId, $userId, $entityId, $answerId, $type){
        $challengeId = null;
        if($type == self::SOLITARY_TYPE){
            $solitary = $this->solitaryDao->findById($entityId);
            $challengeId = $solitary->challengeId;
        }else if($type == self::UNIVERSAL_TYPE){
            $universal = $this->universalDao->findById($entityId);
            $challengeId = $universal->challengeId;
        }

        if(self::SOLITARY_TYPE == $type) {
            $this->updateFinishTimeChallenge($challengeId);
            $solitaryService = FRMCHALLENGE_BOL_SolitaryService::getInstance();
            $solitary = $this->solitaryDao->findById($entityId);
            if ($solitary == null) {
                return null;
            }

            $isOpponent = false;
            if ($solitary->opponentId == $userId) {
                $isOpponent = true;
            }
            $solitaryService->updateSeenBooklet($questionId, $solitary->challengeId, $isOpponent);
            if ($isOpponent) {
                $solitaryService->sendNotificationForAnswerSolitaryChallenge($solitary->userId, $solitary->id, $solitary->opponentId);
            } else {
                $solitaryService->sendNotificationForAnswerSolitaryChallenge($solitary->opponentId, $solitary->id, $solitary->userId);
            }
        }

        return $this->addUserAnswer($questionId, $userId, $challengeId, $answerId);
    }

    public function updateFinishTimeChallenge($challengeId){
        $challenge = $this->challengeDao->findById($challengeId);
        if($challenge != null){
            if($challenge->type == self::UNIVERSAL_TYPE){
                $challenge->finishDate = time() + OW::getConfig()->getValue('frmchallenge', 'universal_answer_time');
            }else{
                $challenge->finishDate = time() + OW::getConfig()->getValue('frmchallenge', 'solitary_answer_time');
            }
            $this->challengeDao->save($challenge);
        }
    }

    public function checkUserPointToInvolve($entityId, $type, $userId){
        $challengeId = null;
        if($type == self::SOLITARY_TYPE){
            $solitary = $this->solitaryDao->findById($entityId);
            $challengeId = $solitary->challengeId;
        }else if($type == self::UNIVERSAL_TYPE){
            $universal = $this->universalDao->findById($entityId);
            if($universal->userId == $userId){
                return true;
            }
            $challengeId = $universal->challengeId;
        }
        $challenge = $this->challengeDao->findById($challengeId);

        if($challenge->minPoint == 0 ){
            return true;
        }

        $user = $this->userDao->findByUserId($userId);
        if($user  == null || $user->point < $challenge->minPoint){
            return false;
        }

        return true;
    }

    /**
     * @param BASE_CLASS_EventCollector $event
     */
    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frmchallenge' => array(
                    'label' => $language->text('frmchallenge', 'challenges'),
                    'actions' => array(
                        'add_universal_challenge' => $language->text('frmchallenge', 'auth_universal_challenge_label'),
                        'add_solitary_challenge' => $language->text('frmchallenge', 'solitary_challenge_label'),
                    )
                )
            )
        );
    }

    public function getUsersPointInfo($count = 10, $currentUserId){
        $users = $this->userDao->getUsersPoint($count);
        $usersInfo = array();
        $currentUserPoint = 0;
        $index = 1;
        $usersInfo['users'] = array();
        foreach ($users as $user){
            if($user->userId == $currentUserId){
                $currentUserPoint = $user->point;
            }
            $data = array(
                "point" => $user->point,
                "username" => BOL_UserService::getInstance()->getDisplayName($user->userId),
                "url" => BOL_UserService::getInstance()->getUserUrl($user->userId),
                "index" => $index
            );

            $index++;

            $usersInfo['users'][] = $data;
        }

        $usersInfo['user'] = array(
            "point" => $currentUserPoint,
            "username" => BOL_UserService::getInstance()->getDisplayName($currentUserId),
            "url" => BOL_UserService::getInstance()->getUserUrl($currentUserId)
        );

        return $usersInfo;
    }

    public function isSolitaryChallengeEnable(){
        if (OW::getConfig()->configExists('frmchallenge', 'solitary_enable') &&
            OW::getConfig()->getValue('frmchallenge', 'solitary_enable') == 1) {
            return true;
        }
        return false;
    }

    public function canUserCreateChallenge(){
        $canCreateSolitary = FRMCHALLENGE_BOL_SolitaryService::getInstance()->canUserCreateSolitary();
        $canCreateUniversal = FRMCHALLENGE_BOL_UniversalService::getInstance()->hasAuthorizeToCreateUniversal(false);
        if($canCreateUniversal || $canCreateSolitary){
            return true;
        }

        return false;
    }

    public function getUsersPoint($count = 10){
        return $this->userDao->getUsersPoint($count);
    }

    /***
     * @param $type
     * @param $categoryId
     * @return bool
     */
    public function checkChallengeHasQuestion($type, $categoryId){
        $count = 0;
        if($type == self::SOLITARY_TYPE){
            $count = OW::getConfig()->getValue('frmchallenge', 'solitary_question_count');
        }else if($type == self::UNIVERSAL_TYPE){
            if(isset($_POST['questions_number'])) {
                $postedCount = $_POST['questions_number'];
                $postedCount = (int) $postedCount;
                if ($postedCount > 0) {
                    $count = $_POST['questions_number'];
                }
            }else {
                $count = OW::getConfig()->getValue('frmchallenge', 'universal_question_count');
            }
        }

        $questions = $this->getRandomQuestionsByCategory($count, $categoryId);
        if(sizeof($questions) < $count){
            return false;
        }

        return true;
    }

}
