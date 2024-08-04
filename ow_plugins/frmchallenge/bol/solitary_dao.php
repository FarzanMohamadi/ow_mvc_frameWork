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
class FRMCHALLENGE_BOL_SolitaryDao extends OW_BaseDao
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
        return 'FRMCHALLENGE_BOL_Solitary';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmchallenge_challenge_solitary';
    }

    public function getSolitaryChallenges($userId){
        $query = "select * from ".$this->getTableName()." where userId = :userId or opponentId = :userId";
        $params = array(":userId" => $userId);
        $result = OW::getDbo()->queryForObjectList($query, $this->getDtoClassName(), $params);
        return $result;
    }

    public function getPublicSolitaryChallenges($userId){
        $query = "select * from ".$this->getTableName()." where userId != :userId and opponentId is null";
        $params = array(":userId" => $userId);
        $result = OW::getDbo()->queryForObjectList($query, $this->getDtoClassName(), $params);
        return $result;
    }

    public function getCreateSolitaryChallengesByUserId($userId){
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        return $this->findListByExample($example);
    }

    /***
     * @param $challengeId
     * @return FRMCHALLENGE_BOL_Solitary
     */
    public function findByChallengeId($challengeId){
        $example = new OW_Example();
        $example->andFieldEqual('challengeId', $challengeId);
        return $this->findObjectByExample($example);
    }
}
