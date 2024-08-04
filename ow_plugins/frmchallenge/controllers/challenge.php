<?php
class FRMCHALLENGE_CTRL_Challenge extends OW_ActionController
{

    public function index($params)
    {
        if(!OW::getUser()->isAuthenticated()){
            throw new Redirect404Exception();
        }

        $service = FRMCHALLENGE_BOL_SolitaryService::getInstance();
        $generalService = FRMCHALLENGE_BOL_GeneralService::getInstance();
        $generalService->addStylesAdnScripts();
        OW::getDocument()->addOnloadScript('create_challenge();');
        $createChallengeForm = $generalService->getChallengeCreationForm();
        $this->addForm($createChallengeForm);

        $result = $generalService->getUserChallengesInfo(OW::getUser()->getId());

        /***
         * Solitary challenges
         */
        $solitaryChallengesRequestFinish = new FRMCHALLENGE_CMP_Challenge($result['solitary_request_finish']);
        $solitaryChallengesRequestSelf = new FRMCHALLENGE_CMP_Challenge($result['solitary_request_self']);
        $solitaryChallengesRequestOpponent = new FRMCHALLENGE_CMP_Challenge($result['solitary_request_opponent']);

        $publicSolitary = $service->getPublicSolitaryChallengesInfo(OW::getUser()->getId());
        $challengesRequestPublic = new FRMCHALLENGE_CMP_Challenge($publicSolitary);

        $this->addComponent('solitary_request_finish', $solitaryChallengesRequestFinish);
        $this->addComponent('solitary_request_self', $solitaryChallengesRequestSelf);
        $this->addComponent('solitary_request_opponent', $solitaryChallengesRequestOpponent);
        $this->addComponent('solitary_request_public', $challengesRequestPublic);

        $this->assign('solitary_request_finish_count', sizeof($result['solitary_request_finish']));
        $this->assign('solitary_request_self_count', sizeof($result['solitary_request_self']));
        $this->assign('solitary_request_opponent_count', sizeof($result['solitary_request_opponent']));
        $this->assign('solitary_request_public_count', sizeof($publicSolitary));
        /***
         * End Solitary challenges
         */

        /***
         * Universal challenges
         */

        $universalChallengesRequestFinish = new FRMCHALLENGE_CMP_Challenge($result['universal_request_finish']);
        $universalChallengesRequestSelf = new FRMCHALLENGE_CMP_Challenge($result['universal_request_self']);
        $universalChallengesRequestPublic = new FRMCHALLENGE_CMP_Challenge($result['universal_request_public']);

        $this->addComponent('universal_request_finish', $universalChallengesRequestFinish);
        $this->addComponent('universal_request_self', $universalChallengesRequestSelf);
        $this->addComponent('universal_request_public', $universalChallengesRequestPublic);

        $this->assign('universal_request_finish_count', sizeof($result['universal_request_finish']));
        $this->assign('universal_request_self_count', sizeof($result['universal_request_self']));
        $this->assign('universal_request_public_count', sizeof($result['universal_request_public']));
        /***
         * End Universal challenges
         */

        /***
         * Users point
         */
        $usersInfo = $generalService->getUsersPointInfo(10, OW::getUser()->getId());
        $this->assign('usersInfo', $usersInfo);
        /***
         * End Users point
         */

        $canCreate = $generalService->canUserCreateChallenge();
        $this->assign('canCreate', $canCreate);

        $solitaryEnable = false;
        if($generalService->isSolitaryChallengeEnable()){
            $solitaryEnable = true;
        }
        $this->assign('solitaryEnable', $solitaryEnable);
    }

    public function finishAnswerTime($params){
        if(!OW::getRequest()->isAjax()){
            throw new Redirect404Exception();
        }

        $data = array('location' => OW_URL_HOME . '404');

        if(!OW::getUser()->isAuthenticated()
            || !isset($params['entityId'])
            || !isset($params['typeId'])
            || !isset($params['questionId'])){
            exit(json_encode($data));
        }

        $entityId = $params['entityId'];
        $questionId = $params['questionId'];
        $userId = OW::getUser()->getId();

        $generalService = FRMCHALLENGE_BOL_GeneralService::getInstance();
        $generalService->checkChallengeTypeValid($params['typeId']);
        $service = FRMCHALLENGE_BOL_SolitaryService::getInstance();

        $canAnswer = $generalService->checkChallengeUserCanAnswer($userId, $entityId, $questionId);
        if(!$canAnswer){
            if($params['typeId'] == FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE){
                $data = array('location' => OW::getRouter()->urlForRoute('frmchallenge.solitary.challenge', array('solitaryId' => $entityId)));
            }else if($params['typeId'] == FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE){
                $data = array('location' => OW::getRouter()->urlForRoute('frmchallenge.universal.challenge', array('universalId' => $entityId)));
            }
            exit(json_encode($data));
        }

        if($params['typeId'] == FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE){
            $access = $service->checkSolitaryChallengePageAccess(OW::getUser()->getId(), $entityId);
            if(!$access){
                exit(json_encode($data));
            }
        }

        $expired = $generalService->processExpiredFinishDateChallenge($entityId, $params['typeId'], true);
        if(!$expired) {
            $generalService->processAddWrongUserAnswer($questionId, $userId, $entityId, $params['typeId']);
        }

        if($params['typeId'] == FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE){
            $data = array('location' => OW::getRouter()->urlForRoute('frmchallenge.solitary.challenge', array('solitaryId' => $entityId)));
        }else if($params['typeId'] == FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE){
            $data = array('location' => OW::getRouter()->urlForRoute('frmchallenge.universal.challenge', array('universalId' => $entityId)));
        }

        exit(json_encode($data));
    }

    public function cancelSolitaryChallenge($params){
        if(!OW::getUser()->isAuthenticated() || !isset($params['solitaryId'])){
            throw new Redirect404Exception();
        }

        $solitaryId = $params['solitaryId'];
        $service = FRMCHALLENGE_BOL_SolitaryService::getInstance();
        $generalService = FRMCHALLENGE_BOL_GeneralService::getInstance();
        $userId = OW::getUser()->getId();

        $canCancel = $service->userCanCancelSolitaryChallenge($userId, $solitaryId);
        if(!$canCancel){
            throw new Redirect404Exception();
        }

        $expired = $generalService->processExpiredFinishDateChallenge($solitaryId, FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE, true);

        if(!$expired) {
            $solitary = $service->cancelSolitaryChallenge($userId, $solitaryId);

            if ($solitary == null) {
                throw new Redirect404Exception();
            }
        }
        $this->redirect(OW::getRouter()->urlForRoute('frmchallenge.index'));
    }

    public function joinChallenge($params){
        if(!OW::getUser()->isAuthenticated()){
            throw new Redirect404Exception();
        }

        if(!isset($params['typeId']) || !isset($params['entityId'])) {
            throw new Redirect404Exception();
        }

        $generalService = FRMCHALLENGE_BOL_GeneralService::getInstance();
        $generalService->checkChallengeTypeValid($params['typeId']);

        if($params['typeId'] == FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE){
            $solitaryId = $params['entityId'];
            $service = FRMCHALLENGE_BOL_SolitaryService::getInstance();
            $userId = OW::getUser()->getId();
            $solitary = $service->joinSolitaryChallenge($userId, $solitaryId);

            if($solitary == null){
                throw new Redirect404Exception();
            }
        }else if($params['typeId'] == FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE){
            //TODO:
        }

        $this->redirect(OW::getRouter()->urlForRoute('frmchallenge.solitary.challenge', array('solitaryId' => $solitaryId)));
    }

    public function solitaryChallenge($params){
        if(!OW::getUser()->isAuthenticated() || !isset($params['solitaryId'])){
            throw new Redirect404Exception();
        }

        if(isset($params['correctNotif']) && $params['correctNotif'] == 'true'){
            OW::getFeedback()->info(OW::getLanguage()->text('frmchallenge', 'correct_answer_info'));
        }else if(isset($params['correctNotif'])){
            $correctNotif = $params['correctNotif'];
            $correctNotifDetails = explode('_', $correctNotif);
            if($correctNotifDetails[0] == 'false'){
                $text = OW::getLanguage()->text('frmchallenge', 'wrong_answer_info');
                $answerId = $correctNotifDetails[1];
                $answer = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->findAnswer($answerId);
                if (isset($answer)){
                    $text .= OW::getLanguage()->text('frmchallenge', 'wrong_answer_info_description', array('title' => $answer->title));
                }
                OW::getFeedback()->warning($text);
            }
        }

        $solitaryId = $params['solitaryId'];
        $service = FRMCHALLENGE_BOL_SolitaryService::getInstance();
        $generalService = FRMCHALLENGE_BOL_GeneralService::getInstance();
        $userId = OW::getUser()->getId();

        $access = $service->checkSolitaryChallengePageAccess($userId, $solitaryId);
        if(!$access){
            throw new Redirect404Exception();
        }

        $generalService->processExpiredFinishDateChallenge($solitaryId, FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE, true);

        $questionInfo = $generalService->getQuestionInfo($solitaryId, FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE, $userId);
        $challengeInfo = $generalService->getChallengeInfo($solitaryId, FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE, $userId);

        $this->assign('challenge', $challengeInfo);
        $this->assign('question', $questionInfo);
        if (isset($questionInfo['form'])) {
            $this->addForm($questionInfo['form']);
            $finishTimeUrl = OW::getRouter()->urlForRoute('frmchallenge.challenge.wrong.answer', array('typeId' => FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE,'entityId' => $solitaryId, 'questionId' => $questionInfo['questionId']));
            OW::getDocument()->addOnloadScript('calculateFinishAnsweringTime(60, "'.$finishTimeUrl.'")');
        }

        $generalService->addStylesAdnScripts();
    }

    public function universalChallenge($params){
        if(!OW::getUser()->isAuthenticated() || !isset($params['universalId'])){
            throw new Redirect404Exception();
        }

        if(isset($params['correctNotif']) && $params['correctNotif'] == 'true'){
            OW::getFeedback()->info(OW::getLanguage()->text('frmchallenge', 'correct_answer_info'));
        }else if(isset($params['correctNotif'])){
            $correctNotif = $params['correctNotif'];
            $correctNotifDetails = explode('_', $correctNotif);
            if($correctNotifDetails[0] == 'false'){
                $answerId = $correctNotifDetails[1];
                $answer = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->findAnswer($answerId);
                $text = OW::getLanguage()->text('frmchallenge', 'wrong_answer_info');
                if (isset($answer)){
                    $text .= OW::getLanguage()->text('frmchallenge', 'wrong_answer_info_description', array('title' => $answer->title));
                }
                OW::getFeedback()->warning($text);
            }
        }

        $universalId = $params['universalId'];
        $universal = FRMCHALLENGE_BOL_UniversalDao::getInstance()->findById($universalId);
        if ($universal == null){
            throw new Redirect404Exception();
        }
        $generalService = FRMCHALLENGE_BOL_GeneralService::getInstance();
        $userId = OW::getUser()->getId();

        $canInvolve = $generalService->checkUserPointToInvolve($universalId, FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE, $userId);
        if(!$canInvolve) {
            throw new Redirect404Exception();
        }

        $generalService->processExpiredFinishDateChallenge($universalId, FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE, true);

        $questionInfo = $generalService->getQuestionInfo($universalId, FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE, $userId);
        $challengeInfo = $generalService->getChallengeInfo($universalId, FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE, $userId);

        $this->assign('challenge', $challengeInfo);
        $this->assign('question', $questionInfo);
        if (isset($questionInfo['form'])) {
            $this->addForm($questionInfo['form']);
            if ($universal->startTime < time()) {
                $finishTimeUrl = OW::getRouter()->urlForRoute('frmchallenge.challenge.wrong.answer', array('typeId' => FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE,'entityId' => $universalId, 'questionId' => $questionInfo['questionId']));
                OW::getDocument()->addOnloadScript('calculateFinishAnsweringTime(60, "'.$finishTimeUrl.'")');
            }
        }

        if($universal->userId == OW::getUser()->getId() || OW::getUser()->isAdmin()){
            $removeLink = OW::getRouter()->urlForRoute('frmchallenge.challenge.remove.universal', array('id' => $universalId));
            $this->assign('userIsManager', true);
            $this->assign('removeLink', $removeLink);
        }

        $generalService->addStylesAdnScripts();
    }

    public function removeUniversal($params){
        if(!OW::getUser()->isAuthenticated()
            || !isset($params['id'])){
            throw new Redirect404Exception();
        }
        $universal = FRMCHALLENGE_BOL_UniversalDao::getInstance()->findById($params['id']);
        if ($universal == null){
            throw new Redirect404Exception();
        }
        if($universal->userId == OW::getUser()->getId() || OW::getUser()->isAdmin()) {
            FRMCHALLENGE_BOL_UniversalService::getInstance()->removeUniversalChallenge($params['id']);
        }else{
            throw new Redirect404Exception();
        }
        $this->redirect(OW::getRouter()->urlForRoute('frmchallenge.index'));
    }

    public function challengeAnswer($params){
        if(!OW::getUser()->isAuthenticated()
            || !isset($params['entityId'])
            || !isset($params['typeId'])
            || !isset($params['questionId'])
            || !isset($_POST['answerId'])){
            throw new Redirect404Exception();
        }

        $entityId = $params['entityId'];
        $questionId = $params['questionId'];
        $answerId = $_POST['answerId'];
        $userId = OW::getUser()->getId();

        $generalService = FRMCHALLENGE_BOL_GeneralService::getInstance();
        $generalService->checkChallengeTypeValid($params['typeId']);
        $service = FRMCHALLENGE_BOL_SolitaryService::getInstance();

        $answerExist = FRMCHALLENGE_BOL_GeneralService::getInstance()->checkAnswerExist($questionId, $answerId);
        if(!$answerExist){
            throw new Redirect404Exception();
        }

        $canAnswer = $generalService->checkChallengeUserCanAnswer($userId, $entityId, $questionId);
        if(!$canAnswer){
            throw new Redirect404Exception();
        }

        if($params['typeId'] == FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE){
            $access = $service->checkSolitaryChallengePageAccess(OW::getUser()->getId(), $entityId);
            if(!$access){
                throw new Redirect404Exception();
            }
        }

        $expired = $generalService->processExpiredFinishDateChallenge($entityId, $params['typeId'], true);
        $correctNotif = 'false_'.$answerId;
        if(!$expired) {
            $generalService->processAddUserAnswer($questionId, $userId, $entityId, $answerId, $params['typeId']);
            $correctAnswerId = $generalService->findCorrectAnswerOfQuestion($questionId);
            if($correctAnswerId == $answerId){
                $correctNotif = 'true';
            }
        }

        if($params['typeId'] == FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE){
            $this->redirect(OW::getRouter()->urlForRoute('frmchallenge.solitary.challenge.notify', array('solitaryId' => $entityId, 'correctNotif' => $correctNotif)));
        }else if($params['typeId'] == FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE){
            $this->redirect(OW::getRouter()->urlForRoute('frmchallenge.universal.challenge.notify', array('universalId' => $entityId, 'correctNotif' => $correctNotif)));
        }

    }

    public function add(){
        if(!OW::getUser()->isAuthenticated()){
            throw new Redirect404Exception();
        }

        $service = FRMCHALLENGE_BOL_SolitaryService::getInstance();
        $generalService = FRMCHALLENGE_BOL_GeneralService::getInstance();

        if(!isset($_POST['challenge_type']) || !$service->checkCorrectChallengeType($_POST['challenge_type'])){
            throw new Redirect404Exception();
        }

        $challengeType = $_POST['challenge_type'];
        $userId = OW::getUser()->getId();
        $postedData = $_POST;

        $categoryId = null;
        if($challengeType == FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE){
            $canCreate = $service->canUserCreateSolitary();
            if(!$canCreate){
                throw new Redirect404Exception();
            }
            if(isset($_POST['categoryId'])){
                $categoryId = $_POST['categoryId'];
            }
        }else if($challengeType == FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE){
            if(isset($_POST['universalCategoryId'])) {
                $categoryId = $_POST['universalCategoryId'];
            }
        }

        $checkChallengeHasQuestion = $generalService->checkChallengeHasQuestion($challengeType, $categoryId);
        if(!$checkChallengeHasQuestion){
            OW::getFeedback()->warning(OW::getLanguage()->text('frmchallenge', 'min_question_count_warning'));
            OW::getApplication()->redirect( OW::getRouter()->urlForRoute('frmchallenge.index'));
        }

        if($challengeType == FRMCHALLENGE_BOL_GeneralService::SOLITARY_TYPE){
            $result = $service->addSolitaryChallenge($postedData, $userId);
            if($result['join']){
                OW::getFeedback()->info(OW::getLanguage()->text('frmchallenge', 'join_successfully'));
            }else if($result['error']){
                OW::getFeedback()->error($result['message']);
            }else{
                OW::getFeedback()->info(OW::getLanguage()->text('frmchallenge', 'create_successfully'));
            }

            if($result['join'] && $result['solitaryId'] != null){
                $this->redirect(OW::getRouter()->urlForRoute('frmchallenge.solitary.challenge', array('solitaryId' => $result['solitaryId'])));
            }
        }else if($challengeType == FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE){
            $accessToCreate = FRMCHALLENGE_BOL_UniversalService::getInstance()->hasAuthorizeToCreateUniversal(false);
            if(!$accessToCreate){
                throw new Redirect404Exception();
            }

            $universalService = FRMCHALLENGE_BOL_UniversalService::getInstance();
            $result = $universalService->addUniversalChallenge($postedData, $userId);
            if($result['error']){
                OW::getFeedback()->error($result['message']);
            }else{
                OW::getFeedback()->info(OW::getLanguage()->text('frmchallenge', 'create_successfully'));
            }
            if($result['universalId'] != null){
                $this->redirect(OW::getRouter()->urlForRoute('frmchallenge.universal.challenge', array('universalId' => $result['universalId'])));
            }
        }

        $this->redirect(OW::getRouter()->urlForRoute('frmchallenge.index'));
    }

    /***
     * @param $params
     * @throws AuthenticateException
     */
    public function loadUsernames($params){
        if (!OW::getUser()->isAuthenticated()) {
            throw new AuthenticateException();
        }

        $username = false;
        if(isset($params['username']))
        {
            $username = $params['username'];
        }

        try {
            //sample
            $data = array();
            $data[] = array('username'=>'imoradnejad', 'fullname'=>'Issa Moradnejad');

            $userPrioritizedIds = $this->findPrioritizedUsers($username, 5);
            $data = $this->getUserInfoForUserIdList(array_unique($userPrioritizedIds));

            exit(json_encode($data));
        }catch(Exception $e){
            exit(json_encode(array('status'=>'error','error_msg'=>OW::getLanguage()->text('base','comment_add_post_error'))));
        }
    }

    /***
     * @param $un
     * @param null $limit
     * @return array
     */
    public function findPrioritizedUsers( $un, $limit = null)
    {
        if(!OW::getPluginManager()->isPluginActive('friends'))
            return array();

        $userId = OW::getUser()->getId();
        $limitStr = $limit === null ? '' : 'LIMIT 0, ' . intval($limit*2);

        //SELECT FROM FRIENDS
        $query = "SELECT DISTINCT id
            FROM ".OW_DB_PREFIX."base_user
            WHERE id IN (
                SELECT DISTINCT userId
                FROM ".OW_DB_PREFIX."friends_friendship
                WHERE friendId=".$userId." AND status='active'
                UNION
                SELECT DISTINCT friendId
                FROM ".OW_DB_PREFIX."friends_friendship
                WHERE userId=".$userId." AND status='active'
            )
            AND username like :un ". $limitStr;

        $all_users = OW::getDbo()->queryForColumnList($query, array( 'un' =>  '%'.$un . '%'  ));
        return $all_users;
    }

    /***
     * @param $userIdList
     * @return array
     */
    public function getUserInfoForUserIdList( $userIdList )
    {
        if (empty($userIdList))
        {
            return array();
        }

        $userInfoList = array();
        $userNameByUserIdList = BOL_UserService::getInstance()->getUserNamesForList($userIdList);
        $displayNameByUserIdList = BOL_UserService::getInstance()->getDisplayNamesForList($userIdList);
        foreach ($userIdList as $opponentId)
        {
            $info = array(
                'username' => $userNameByUserIdList[$opponentId],
                'fullname' => $displayNameByUserIdList[$opponentId]
            );
            $userInfoList[] = $info;
        }
        return $userInfoList;
    }
}