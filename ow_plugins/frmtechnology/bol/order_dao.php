<?php
class FRMTECHNOLOGY_BOL_OrderDao extends OW_BaseDao
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
        return 'FRMTECHNOLOGY_BOL_Order';
    }


    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmtechnology_order';
    }

    public function findOrders($first,$count){
        $technologyTable = FRMTECHNOLOGY_BOL_TechnologyDao::getInstance()->getTableName();
        $sql = 'SELECT o.`id`,o.`name`,o.`description`,o.`technologyId`,o.`timeStamp`, t.`title` AS technologyTitle, t.`status` AS technologyStatus FROM ' . $this->getTableName() . ' AS o
                    INNER JOIN ' . $technologyTable . ' AS t ON o.technologyId = t.id LIMIT :first, :count';
        return OW::getDbo()->queryForList($sql,array('first'=>$first,'count'=>$count));

    }
    public function findOrdersCount(){
        $technologyTable = FRMTECHNOLOGY_BOL_TechnologyDao::getInstance()->getTableName();
        $sql = 'SELECT COUNT(*) FROM ' . $this->getTableName() . ' AS o
                    INNER JOIN ' . $technologyTable . ' AS t ON o.technologyId = t.id';
        return OW::getDbo()->queryForColumn($sql);

    }


    public function findOrdersByTechnologyId($technologyId,$first,$count)
    {
        $technologyTable = FRMTECHNOLOGY_BOL_TechnologyDao::getInstance()->getTableName();
        $sql = 'SELECT o.`id`,o.`name`,o.`description`,o.`technologyId`,o.`timeStamp`, t.`title` AS technologyTitle, t.`status` AS technologyStatus FROM ' . $this->getTableName() . ' AS o
                    INNER JOIN ' . $technologyTable . ' AS t ON o.technologyId = t.id WHERE o.`technologyId` = :technologyId LIMIT :first, :count';
        return OW::getDbo()->queryForList($sql, array('technologyId'=>$technologyId,'first'=>$first,'count'=>$count));

    }

    public function findOrderCountByTechnologyId($technologyId)
    {
        $query = "SELECT COUNT(*) FROM `" . $this->getTableName() . "` WHERE technologyId = :technologyId ";
        return $this->dbo->queryForColumn($query,array('technologyId' => $technologyId));

    }

}