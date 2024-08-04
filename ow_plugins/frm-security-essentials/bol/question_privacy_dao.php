<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsecurityessentials.bol
 * @since 1.0
 */
class FRMSECURITYESSENTIALS_BOL_QuestionPrivacyDao extends OW_BaseDao
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'FRMSECURITYESSENTIALS_BOL_QuestionPrivacy';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmsecurityessentials_question_privacy';
    }

    /***
     * @param $userId
     * @param $questionId
     * @return mixed
     */
    public function getQuestionPrivacy($userId, $questionId){
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        $ex->andFieldEqual('questionId', $questionId);
        $questionPrivacy = $this->findObjectByExample($ex);
        if($questionPrivacy==null){
            return null;
        }
        return $questionPrivacy->privacy;
    }

    /***
     * @param $userId
     * @param $questionIds
     * @return mixed
     */
    public function getQuestionsPrivacy($userId, $questionIds){
        if (empty($questionIds)) {
            return array();
        }
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        $ex->andFieldInArray('questionId', $questionIds);
        $questionsPrivacy = $this->findListByExample($ex);
        $result = array();
        foreach ($questionsPrivacy as $questionPrivacy) {
            $result[$questionPrivacy->questionId] = $questionPrivacy;
        }
        return $result;
    }

    /***
     * @param $userIds
     * @param $questionIds
     * @return array
     */
    public function getQuestionsPrivacyForUserList($userIds, $questionIds){
        if (empty($questionIds) || empty($userIds)) {
            return array();
        }
        $ex = new OW_Example();
        $ex->andFieldInArray('userId', $userIds);
        $ex->andFieldInArray('questionId', $questionIds);
        $questionsPrivacy = $this->findListByExample($ex);
        $result = array();
        foreach ($questionsPrivacy as $questionPrivacy) {
            if(!isset($result[$questionPrivacy->userId])){
                $result[$questionPrivacy->userId] = [];
            }
            $result[$questionPrivacy->userId][$questionPrivacy->questionId] = $questionPrivacy;
        }
        return $result;
    }

    /***
     * @param $userIds
     * @param $privacy
     * @param $questionId
     * @return array
     */
    public function getQuestionsPrivacyByExceptPrivacy($userIds, $privacy, $questionId){
        if(!is_array($userIds) || empty($userIds)){
            return array();
        }
        $ex = new OW_Example();
        $ex->andFieldInArray('userId', $userIds);
        $ex->andFieldNotEqual('privacy', $privacy);
        $ex->andFieldEqual('questionId', $questionId);
        return $this->findListByExample($ex);
    }

    /***
     * @param $userId
     * @param $questionId
     * @param $privacy
     * @return FRMSECURITYESSENTIALS_BOL_QuestionPrivacy
     */
    public function setQuestionPrivacy($userId, $questionId, $privacy){
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        $ex->andFieldEqual('questionId', $questionId);
        $questionPrivacy = $this->findObjectByExample($ex);

        if($questionPrivacy==null) {
            $questionPrivacy = new FRMSECURITYESSENTIALS_BOL_QuestionPrivacy();
            $questionPrivacy->userId = $userId;
            $questionPrivacy->questionId = $questionId;
        }
        $questionPrivacy->privacy = $privacy;
        $this->save($questionPrivacy);
        return $questionPrivacy;
    }



}
