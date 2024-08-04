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
class FRMCHALLENGE_BOL_UniversalDao extends OW_BaseDao
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
        return 'FRMCHALLENGE_BOL_Universal';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmchallenge_challenge_universal';
    }

    public function getUniversalChallengesByUserId($userId){
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        return $this->findListByExample($example);
    }

    /***
     * @param $challengeId
     * @return FRMCHALLENGE_BOL_Universal
     */
    public function findByChallengeId($challengeId){
        $example = new OW_Example();
        $example->andFieldEqual('challengeId', $challengeId);
        return $this->findObjectByExample($example);
    }

    public function getUniversalChallenges(){
        $example = new OW_Example();
        $example->setOrder('`startTime` ASC');
        return $this->findListByExample($example);
    }
}
