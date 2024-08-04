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
class FRMCHALLENGE_BOL_UniversalService
{
    private static $classInstance;
    private $challengeDao;
    private $universalDao;

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
        $this->universalDao = FRMCHALLENGE_BOL_UniversalDao::getInstance();
    }

    public function hasAuthorizeToCreateUniversal($redirect = false){
        if(!OW::getUser()->isAuthorized('frmchallenge','add_universal_challenge') && !OW::getUser()->isAdmin()){
            if($redirect){
                throw new Redirect404Exception();
            }else{
                return false;
            }
        }

        return true;
    }

    public function removeUniversalChallenge($id){
        $universal = $this->universalDao->findById($id);
        if ($universal != null){
            $this->challengeDao->deleteById($universal->challengeId);
            $this->universalDao->deleteById($id);
        }
    }

    public function addUniversalChallenge($data, $userId){
        $result['error'] = false;
        $result['message'] = "";
        $result['universalId'] = null;

        $data['type'] = FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE;
        $data['categoryId'] = $data['universalCategoryId'];

        $result = FRMCHALLENGE_BOL_GeneralService::getInstance()->addChallenge($data);
        if (!$result['error']) {
            $challenge = $result['challenge'];
            $universalChallenge = new FRMCHALLENGE_BOL_Universal();
            $universalChallenge->userId = $userId;
            $universalChallenge->challengeId = $challenge->id;
            if(isset($data['win_num'])){
                $universalChallenge->winNum = $data['win_num'];
            }else{
                $universalChallenge->winNum = 10;
            }
            if(isset($data['questions_number'])){
                $universalChallenge->questionsNumber = (int) $data['questions_number'];
            }else{
                $universalChallenge->questionsNumber = 10;
            }
            $form = FRMCHALLENGE_BOL_GeneralService::getInstance()->getChallengeCreationForm();
            $form->isValid($_POST);
            $formDataValue = $form->getValues();
            if(isset($formDataValue['start_date']) && !empty($formDataValue['start_date'])){
                $dateArray = explode('/', $formDataValue['start_date']);
                $startStamp = mktime(0, 0, 0, (int) $dateArray[1], (int) $dateArray[2], (int) $dateArray[0]);
                $universalChallenge->startTime = $startStamp;
            }else{
                $universalChallenge->startTime = time();
            }
            $this->universalDao->save($universalChallenge);
            FRMCHALLENGE_BOL_GeneralService::getInstance()->startChallenge($challenge->id);

            $result['universalChallenge'] = $universalChallenge;
            $result['universalId'] = $universalChallenge->id;
        }

        return $result;
    }

    public function finishUniversalChallenge($universalId){
        $universal = $this->universalDao->findById($universalId);
        $challenge = $this->challengeDao->findById($universal->challengeId);
        if($challenge->status != FRMCHALLENGE_BOL_GeneralService::STATUS_FINISH) {
            $challenge->status = FRMCHALLENGE_BOL_GeneralService::STATUS_FINISH;
            $this->challengeDao->save($challenge);

            $winnerInfo = $this->findUniversalWinner($universalId);
            if($winnerInfo['userId'] != -1) {
                FRMCHALLENGE_BOL_GeneralService::getInstance()->addUserPoint($winnerInfo['userId'], $challenge->winPoint);
            }
        }
    }

    public function findUniversalUserPoints($universalId, $count){
        if($count == null){
            $count = 10;
        }
        $universal = $this->universalDao->findById($universalId);
        $users = FRMCHALLENGE_BOL_UserAnswerDao::getInstance()->findUsers($universal->challengeId);
        $usersPoint = array();
        $userCountFind = $count;

        while($userCountFind > 0 ){
            $maxPoint = -1;
            $winnerId = -1;
            $userCountFind--;
            foreach ($users as $user){
                $userPoint = FRMCHALLENGE_BOL_GeneralService::getInstance()->findPointOfUserInChallenge($universalId, FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE, $user['userId'], $universal->challengeId);
                if(!key_exists($user['userId'], $usersPoint) && $userPoint > $maxPoint){
                    $maxPoint = $userPoint;
                    $winnerId = $user['userId'];
                }
            }

            if($winnerId != -1){
                $user = BOL_UserService::getInstance()->findUserById($winnerId);
                $usersPoint[$winnerId] = array(
                    "point" => $maxPoint,
                    "username" =>  $user->getUsername(),
                    "url" => BOL_UserService::getInstance()->getUserUrl($winnerId),
                    "index" => sizeof($usersPoint) + 1
                );
            }
        }

        return $usersPoint;
    }

    public function findUniversalWinner($universalId){
        $universal = $this->universalDao->findById($universalId);
        $users = FRMCHALLENGE_BOL_UserAnswerDao::getInstance()->findUsers($universal->challengeId);
        $maxPoint = 0;
        $winnerId = -1;
        foreach ($users as $user){
            $userPoint = FRMCHALLENGE_BOL_GeneralService::getInstance()->findPointOfUserInChallenge($universalId, FRMCHALLENGE_BOL_GeneralService::UNIVERSAL_TYPE, $user['userId'], $universal->challengeId);
            if($userPoint > $maxPoint){
                $maxPoint = $userPoint;
                $winnerId = $user['userId'];
            }
        }

        return array('userId' => $winnerId, 'point' => $maxPoint);
    }
}
