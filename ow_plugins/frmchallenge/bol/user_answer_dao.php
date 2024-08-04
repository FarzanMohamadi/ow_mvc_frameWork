<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmchallenge.bol
 * @since 1.0
 */
class FRMCHALLENGE_BOL_UserAnswerDao extends OW_BaseDao
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

    public function getDtoClassName()
    {
        return 'FRMCHALLENGE_BOL_UserAnswer';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmchallenge_challenge_user_answer';
    }

    public function findUserAnswer($questionId, $userId, $challengeId){
        $example = new OW_Example();
        $example->andFieldEqual('questionId' , $questionId);
        $example->andFieldEqual('userId' , $userId);
        $example->andFieldEqual('challengeId' , $challengeId);
        return $this->findObjectByExample($example);
    }

    public function addUserAnswer($questionId, $userId, $challengeId, $answerId){
        $answer = new FRMCHALLENGE_BOL_UserAnswer();
        $answer->questionId = $questionId;
        $answer->userId = $userId;
        $answer->challengeId = $challengeId;
        $answer->answerId = $answerId;
        $this->save($answer);
        return $answer;
    }

    public function findUsers($challengeId){
        $query = " SELECT DISTINCT userId FROM " . $this->getTableName() . " where challengeId = :challengeId";
        $param = array(":challengeId" => $challengeId);
        return $this->dbo->queryForList($query, $param);
    }
}
