<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcompetition.bol
 * @since 1.0
 */
class FRMCOMPETITION_BOL_CompetitionUserDao extends OW_BaseDao
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
        return 'FRMCOMPETITION_BOL_CompetitionUser';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmcompetition_competition_user';
    }

    /***
     * @param $competitionId
     * @return array
     */
    public function findCompetitionUsers($competitionId){
        $ex = new OW_Example();
        $ex->andFieldEqual('competitionId', $competitionId);
        $ex->setOrder('`value` DESC');
        return $this->findListByExample($ex);
    }

    /***
     * @param $userId
     * @param $competitionId
     * @param $value
     * @return FRMCOMPETITION_BOL_CompetitionUser|mixed
     */
    public function saveCompetitionUsers($userId, $competitionId, $value){
        $ex = new OW_Example();
        $ex->andFieldEqual('competitionId', $competitionId);
        $ex->andFieldEqual('userId', $userId);
        $competitionUser = $this->findObjectByExample($ex);

        if($competitionUser == null){
            $competitionUser = new FRMCOMPETITION_BOL_CompetitionUser();
        }

        $competitionUser->value = $value;
        $competitionUser->userId = $userId;
        $competitionUser->competitionId = $competitionId;
        $this->save($competitionUser);
        return $competitionUser;
    }
}
