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
class FRMCHALLENGE_BOL_BookletDao extends OW_BaseDao
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
        return 'FRMCHALLENGE_BOL_Booklet';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmchallenge_booklet';
    }

    public function addBooklet($questionId, $challengeId){
        $booklet = new FRMCHALLENGE_BOL_Booklet();
        $booklet->questionId = $questionId;
        $booklet->challengeId = $challengeId;
        $booklet->userIdSeen = 0;
        $booklet->opponentIdSeen = 0;
        $this->save($booklet);
        return $booklet;
    }

    /***
     * @param $questionId
     * @param $challengeId
     * @return FRMCHALLENGE_BOL_Booklet
     */
    public function findBooklet($questionId, $challengeId){
        $example = new OW_Example();
        $example->andFieldEqual('questionId', $questionId);
        $example->andFieldEqual('challengeId', $challengeId);
        return $this->findObjectByExample($example);
    }

    /***
     * @param $challengeId
     * @return array
     */
    public function findBooklets($challengeId){
        $example = new OW_Example();
        $example->andFieldEqual('challengeId', $challengeId);
        $example->setOrder('id ASC');
        return $this->findListByExample($example);
    }

    public function updateSeenBooklet($questionId, $challengeId, $isOpponent = false){
        $booklet = $this->findBooklet($questionId, $challengeId);
        if($isOpponent){
            $booklet->opponentIdSeen = 1;
        }else{
            $booklet->userIdSeen = 1;
        }

        $this->save($booklet);
    }

    public function addBooklets($questionsId, $challengeId){
        $data = array();
        foreach ($questionsId as $questionId){
            $booklet = new FRMCHALLENGE_BOL_Booklet();
            $booklet->questionId = $questionId;
            $booklet->challengeId = $challengeId;
            $booklet->userIdSeen = 0;
            $booklet->opponentIdSeen = 0;
            $this->save($booklet);
            $data[] = $booklet;
        }

        return $data;
    }
}
