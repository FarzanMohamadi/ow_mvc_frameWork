<?php
/**
 * Created by PhpStorm.
 * User: ismail
 * Date: 1/3/18
 * Time: 1:13 PM
 */
class FRMFARAPAYAMAK_BOL_TrackDao extends OW_BaseDao
{

    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmfarapayamak_track';
    }

    public function getDtoClassName()
    {
        return 'FRMFARAPAYAMAK_BOL_Track';
    }

    public function findAllTracks($page = 1,$limit = 10,$orderedByTime = true){
        $example = new OW_Example();
        if ($orderedByTime)
            $example->setOrder('time desc');
        $example->setLimitClause(($page-1)*$limit, $limit);
        return $this->findListByExample($example);
    }

    public function findAllTrackCount(){
        return $this->countAll();
    }
}