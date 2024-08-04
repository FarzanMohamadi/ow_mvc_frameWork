<?php
class FRMTECHNOLOGY_BOL_TechnologyDao extends OW_BaseDao
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
        return 'FRMTECHNOLOGY_BOL_Technology';
    }


    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmtechnology_technology';
    }




    public function findTechnologiesByFiltering($searchTitle , $first,$count)
    {
        $ex = new OW_Example();
        if(isset($searchTitle)) {
            $ex->andFieldLike('title', '%' . $searchTitle . '%');
        }
        else{
            $ex->andFieldEqual('status' ,FRMTECHNOLOGY_BOL_Service::STATUS_ACTIVE);
        }
        $ex->setLimitClause($first, $count);
        return $this->findListByExample($ex);

    }

    public function findTechnologiesByFilteringCount($searchTitle)
    {
        $params=array();
        $whereClause="WHERE 1=1";
        if(isset($searchTitle))
        {
            $params['searchTitle']='%'.$searchTitle.'%';
            $whereClause=$whereClause. " AND `title` like :searchTitle";
        }
        $query = "SELECT COUNT(*) FROM `" . $this->getTableName() . "`".$whereClause;
        return $this->dbo->queryForColumn($query,$params);

    }

    public function findTechnologies( $first, $count )
    {
        $params = array( 'first' => (int) $first, 'count' => (int) $count);
            $query = "SELECT * FROM `" . $this->getTableName() . "`ORDER BY `id` DESC LIMIT :first, :count";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $params);
    }

    public function findTechnologiesCount()
    {
        $query = "SELECT COUNT(*) FROM `" . $this->getTableName() . "`";
        return $this->dbo->queryForColumn($query);
    }

//    public function findMyTechnologies( $first, $count )
//    {
//        $params = array( 'userId' => OW::getUser()->getId(), 'first' => (int) $first, 'count' => (int) $count);
//        $query = "SELECT * FROM `" . $this->getTableName() . "` WHERE userId = :userId ORDER BY `id` DESC LIMIT :first, :count";
//
//        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $params);
//    }

//    public function findMyTechnologiesCount()
//    {
//        $query = "SELECT COUNT(*) FROM `" . $this->getTableName() . "` WHERE userId = :userId ";
//        return $this->dbo->queryForColumn($query,array('userId' => OW::getUser()->getId()));
//    }

    public function findOrderedList( $first, $count )
    {
        $first = (int) $first;
        $count = (int) $count;
        $example = new OW_Example();
        $example->setOrder('`timeStamp` DESC');
        $example->setLimitClause($first, $count);
        $example->andFieldEqual("status", FRMTECHNOLOGY_BOL_Service::STATUS_ACTIVE);
        return $this->findListByExample($example);

    }
    public function findDeactivateOrderedList( $first, $count )
    {
        $first = (int) $first;
        $count = (int) $count;
        $example = new OW_Example();
        $example->setOrder('`timeStamp` DESC');
        $example->setLimitClause($first, $count);
        $example->andFieldEqual("status", FRMTECHNOLOGY_BOL_Service::STATUS_DEACTIVATE);
        return $this->findListByExample($example);

    }
    public function findOrderedListCount()
    {
        $query = "SELECT COUNT(*) FROM `" . $this->getTableName() . "` WHERE status = :status ";
        return $this->dbo->queryForColumn($query,array('status' => FRMTECHNOLOGY_BOL_Service::STATUS_ACTIVE));
    }
    public function findDeactivateOrderedListCount()
    {
        $query = "SELECT COUNT(*) FROM `" . $this->getTableName() . "` WHERE status = :status ";
        return $this->dbo->queryForColumn($query,array('status' => FRMTECHNOLOGY_BOL_Service::STATUS_DEACTIVATE));
    }
    public function findTechnologyListByTag( $tag, $first, $count )
    {
        $entityType = 'technology-description';
        $privacyConditionWhere = '';
        $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('tagEntityTableName' => '`et`', 'entityType' => $entityType, 'listType' => 'tag')));
        if(isset($privacyConditionEvent->getData()['where'])){
            $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
        }
        $query = "SELECT `et`.`" . BOL_EntityTagDao::ENTITY_ID . "` AS `id` from `" . BOL_TagDao::getInstance()->getTableName() . "` AS `t` 
                    INNER JOIN `" . BOL_EntityTagDao::getInstance()->getTableName() . "` AS `et` ON(`et`.`" . BOL_EntityTagDao::TAG_ID . "`=`t`.`id`)
                    LEFT JOIN `".$this->getTableName()."` AS `tech` ON `tech`.`id` = `et`.`entityId`
                WHERE `t`.`" . BOL_TagDao::LABEL . "` LIKE :tag AND `tech`.`status` = '". FRMTECHNOLOGY_BOL_Service::STATUS_ACTIVE ."' AND `et`.`" . BOL_EntityTagDao::ENTITY_TYPE . "` = :entityType AND `et`.`" . BOL_EntityTagDao::ACTIVE . "` = 1" . $privacyConditionWhere . "
                ORDER BY `et`.`entityId` DESC
                LIMIT :first, :count";
        $params = array('tag' => '%'.$tag.'%', 'entityType' => $entityType, 'first' => (int) $first, 'count' => (int) $count);
        if(isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $params = array_merge($params, $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForColumnList($query, $params);
    }
    public function findTechnologyCountByTag( $tag )
    {
        $entityType = 'technology-description';
        $query = "SELECT COUNT(*) from `" . BOL_TagDao::getInstance()->getTableName() . "` AS `t` 
                    INNER JOIN `" . BOL_EntityTagDao::getInstance()->getTableName() . "` AS `et` ON(`et`.`" . BOL_EntityTagDao::TAG_ID . "`=`t`.`id`)
                    LEFT JOIN `".$this->getTableName()."` AS `tech` ON `tech`.`id` = `et`.`entityId`
                where `t`.`" . BOL_TagDao::LABEL . "` = :tag AND `et`.`" . BOL_EntityTagDao::ENTITY_TYPE . "` = :entityType AND `et`.`" . BOL_EntityTagDao::ACTIVE . "` = 1";

        return (int) $this->dbo->queryForColumn($query, array('tag' => $tag, 'entityType' => $entityType));
    }
}