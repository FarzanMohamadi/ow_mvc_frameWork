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
class FRMCOMPETITION_BOL_CompetitionDao extends OW_BaseDao
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
        return 'FRMCOMPETITION_BOL_Competition';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmcompetition_competition';
    }

    /***
     * @param int $first
     * @param int $count
     * @return array
     */
    public function findCompetitions($first = 0, $count = 20){
        $ex = new OW_Example();
        $ex->setOrder('`dateCreated` DESC');
        $ex->setLimitClause($first,$count);
        return $this->findListByExample($ex);
    }

    /***
     * @return array
     */
    public function findAllCompetitions(){
        $ex = new OW_Example();
        $ex->setOrder('`dateCreated` DESC');
        return $this->findListByExample($ex);
    }

    /***
     * @param $id
     * @return mixed
     */
    public function findCompetitionById($id){
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $id);
        return $this->findObjectByExample($ex);
    }

    /***
     * @param $id
     * @return int
     */
    public function deleteCompetitionById($id){
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $id);
        return $this->deleteByExample($ex);
    }

    /***
     * @param $title
     * @param $description
     * @param $active
     * @param $image
     * @param $startDate
     * @param $endDate
     * @param $type
     * @param null $competitionId
     * @return FRMCOMPETITION_BOL_Competition|mixed|null
     */
    public function saveCompetition($title, $description, $active, $image, $startDate, $endDate, $type, $competitionId = null){
        $competition = null;
        if($competitionId!=null){
            $ex = new OW_Example();
            $ex->andFieldEqual('id', $competitionId);
            $competition = $this->findObjectByExample($ex);
        }

        if($competition == null){
            $competition = new FRMCOMPETITION_BOL_Competition();
        }

        $competition->title = $title;
        $competition->description = $description;
        $competition->active = $active;
        $competition->image = $image;
        $competition->startDate = $startDate;
        $competition->endDate = $endDate;
        $competition->type = $type;
        $competition->dateCreated = time();
        $this->save($competition);
        return $competition;
    }
}
