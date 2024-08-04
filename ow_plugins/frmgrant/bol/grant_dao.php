<?php
class FRMGRANT_BOL_GrantDao extends OW_BaseDao
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

    protected function __construct()
    {
        parent::__construct();
    }

    public function getDtoClassName()
    {
        return 'FRMGRANT_BOL_Grant';
    }


    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmgrant_grant';
    }
    public function findGrantsCount()
    {
        $query = "SELECT COUNT(*) FROM `" . $this->getTableName() . "`";
        return $this->dbo->queryForColumn($query);
    }
    public function findOrderedList( $first, $count )
    {
        $first = (int) $first;
        $count = (int) $count;
        $example = new OW_Example();
        $example->setOrder('`timeStamp` DESC');
        $example->setLimitClause($first, $count);
        return $this->findListByExample($example);
    }
}
