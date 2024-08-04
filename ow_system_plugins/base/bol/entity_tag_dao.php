<?php
/**
 * Data Access Object for `base_entity_tag` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_EntityTagDao extends OW_BaseDao
{
    const ENTITY_ID = 'entityId';
    const ENTITY_TYPE = 'entityType';
    const TAG_ID = 'tagId';
    const ACTIVE = 'active';

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_EntityTagDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_EntityTagDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_EntityTag';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_entity_tag';
    }

    /**
     * Deletes entity_tag items for provided params.
     *
     * @param integer $entityId
     * @param string $entityType
     */
    public function deleteItemsForEntityItem( $entityId, $entityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);

        $this->deleteByExample($example);
    }

    /**
     * Deletes entity_tag items for provided params.
     *
     * @param integer $entityId
     * @param string $entityType
     * @param integer $tagId
     */
    public function deleteEntityTagItem( $entityId, $entityType, $tagId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);
        $example->andFieldEqual(self::TAG_ID, $tagId);

        $this->deleteByExample($example);
    }

    /**
     * Returns entity_tag items for provided params.
     *
     * @param integer $entityId
     * @param string $entityType
     * @return array
     */
    public function findEntityTagItems( $entityId, $entityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);

        return $this->findListByExample($example);
    }

    public function findEntityTagItemsByEntityIds( $entityIds, $entityType )
    {
        if (empty($entityIds)) {
            return array();
        }
        $example = new OW_Example();
        $example->andFieldInArray(self::ENTITY_ID, $entityIds);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);
        $res = $this->findListByExample($example);

        $tagIds = array();

        /* @var $entityTag BOL_EntityTag */
        foreach ( $res as $entityTag )
        {
            $tagIds[] = $entityTag->tagId;
        }

        $tags = BOL_TagDao::getInstance()->findByIdList($tagIds);

        $data = array();
        foreach ($tags as $tag) {
            $tagFound = null;
            $entityTagFound = null;

            foreach ($res as $resItem) {
                if ($resItem->tagId == $tag->id) {
                    $tagFound = $tag;
                    $entityTagFound = $resItem;
                }
            }

            if ($tagFound != null) {
                $data[$entityTagFound->entityId][] = $tagFound;
            }
        }
        foreach ($entityIds as $entityId) {
            if (!isset($data[$entityId])) {
                $data[$entityId] = array();
            }
        }

        return $data;
    }

    public function updateEntityStatus( $entityType, $entityId, $status )
    {
        $query = "UPDATE `" . $this->getTableName() . "` SET `" . self::ACTIVE . "` = :status
                WHERE `" . self::ENTITY_TYPE . "` = :entityType AND `" . self::ENTITY_ID . "` = :entityId";

        $this->dbo->query($query, array('status' => $status, 'entityType' => $entityType, 'entityId' => $entityId));
    }

    public function findEntityListByTag( $entityType, $tag, $first, $count )
    {
        $privacyConditionWhere = '';
        $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('tagEntityTableName' => '`et`', 'entityType' => $entityType, 'listType' => 'tag')));
        if(isset($privacyConditionEvent->getData()['where'])){
            $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
        }
        $query = "SELECT `et`.`" . self::ENTITY_ID . "` AS `id` from `" . BOL_TagDao::getInstance()->getTableName() . "` AS `t` INNER JOIN `" . $this->getTableName() . "` AS `et` ON(`et`.`" . self::TAG_ID . "`=`t`.`id`)
                WHERE `t`.`" . BOL_TagDao::LABEL . "` LIKE :tag AND `et`.`" . self::ENTITY_TYPE . "` = :entityType AND `et`.`" . self::ACTIVE . "` = 1" . $privacyConditionWhere . "
                ORDER BY `et`.`entityId` DESC
                LIMIT :first, :count";
        $params = array('tag' => '%'.$tag.'%', 'entityType' => $entityType, 'first' => (int) $first, 'count' => (int) $count);
        if(isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $params = array_merge($params, $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForColumnList($query, $params);
    }

    public function findEntityCountByTag( $entityType, $tag )
    {
        $query = "SELECT COUNT(*) from `" . BOL_TagDao::getInstance()->getTableName() . "` AS `t` INNER JOIN `" . $this->getTableName() . "` AS `et` ON(`et`.`" . self::TAG_ID . "`=`t`.`id`)
                where `t`.`" . BOL_TagDao::LABEL . "` = :tag AND `et`.`" . self::ENTITY_TYPE . "` = :entityType AND `et`.`" . self::ACTIVE . "` = 1";

        return (int) $this->dbo->queryForColumn($query, array('tag' => $tag, 'entityType' => $entityType));
    }

    public function deleteByEntityType( $entityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_TYPE, trim($entityType));

        $this->deleteByExample($example);
    }

    public function findEntityIdListByTagIdList( array $idList )
    {
        if ( empty($idList) )
        {
            return array();
        }

        $sql = 'SELECT `' . self::ENTITY_ID . '`
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::TAG_ID . '` IN (' . $this->dbo->mergeInClause($idList) . ')';

        return $this->dbo->queryForColumnList($sql);
    }
}