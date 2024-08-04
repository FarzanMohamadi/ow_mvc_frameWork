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
class FRMCOMPETITION_BOL_CompetitionGroupDao extends OW_BaseDao
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
        return 'FRMCOMPETITION_BOL_CompetitionGroup';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmcompetition_competition_group';
    }

    /***
     * @param $competitionId
     * @return array
     */
    public function findCompetitionGroups($competitionId){
        $ex = new OW_Example();
        $ex->andFieldEqual('competitionId', $competitionId);
        $ex->setOrder('`value` DESC');
        return $this->findListByExample($ex);
    }

    /***
     * @param $groupId
     * @param $competitionId
     * @param $value
     * @return FRMCOMPETITION_BOL_CompetitionGroup|mixed
     */
    public function saveCompetitionGroup($groupId, $competitionId, $value){
        $ex = new OW_Example();
        $ex->andFieldEqual('competitionId', $competitionId);
        $ex->andFieldEqual('groupId', $groupId);
        $competitionGroup = $this->findObjectByExample($ex);

        if($competitionGroup == null){
            $competitionGroup = new FRMCOMPETITION_BOL_CompetitionGroup();
        }

        $competitionGroup->value = $value;
        $competitionGroup->groupId = $groupId;
        $competitionGroup->competitionId = $competitionId;
        $this->save($competitionGroup);
        return $competitionGroup;
    }
}
