<?php
/**
 * Data Access Object for `tag` table.  
 * 
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_TagDao extends OW_BaseDao
{
    // table field names
    const LABEL = 'label';

    /**
     * Singleton instance.
     *
     * @var BOL_TagDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_TagDao
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
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_Tag';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_tag';
    }

    /**
     * Returns dto list for provided tag labels.
     *
     * @param array<string>$labels
     * @return array
     */
    public function findTagsByLabel( $labels )
    {
        $example = new OW_Example();
        $example->andFieldInArray(self::LABEL, $labels);

        return $this->findListByExample($example);
    }

    public function findTagListByEntityIdList( $entityType, array $idList )
    {
        $query = "SELECT `t`.`label`, `et2`.*  FROM " . $this->getTableName() . " AS `t`
INNER JOIN (
    SELECT * FROM `" . BOL_EntityTagDao::getInstance()->getTableName() . "` AS `et`
    WHERE `et`.`entityId` IN (" . $this->dbo->mergeInClause($idList) . ") AND `et`.`entityType` = :entityType
) AS `et2` ON ( `et2`.`tagId` = `t`.`id` )";

        return $this->dbo->queryForList($query, array('entityType' => $entityType));
    }

    /**
     * Returns most popular tags for entity type.
     * 
     * @param string $entityType
     * @param integer $limit
     * @param integer $offset
     * @return array
     */
    public function findMostPopularTags( $entityType, $limit, $offset  = 0)
    {
        $privacyConditionWhere = '';
        $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('tagEntityTableName' => '`et`', 'entityType' => $entityType, 'listType' => 'tag')));
        if(isset($privacyConditionEvent->getData()['where'])){
            $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
        }
        $query = "SELECT * FROM
            (
                SELECT `t`.`id` as id, COUNT(*) AS `count`, `t`.`label` AS `label` FROM `" . BOL_EntityTagDao::getInstance()->getTableName() . "` AS `et`
                LEFT JOIN `" . $this->getTableName() . "` AS `t` ON ( `et`.`tagId` = `t`.`id`	)
                WHERE `et`.`entityType` = :entityType AND `et`.`active` = 1 ".$privacyConditionWhere."
                GROUP BY `t`.`id`
                                    ORDER BY `count` DESC
                                    LIMIT :offset, :limit
            ) AS `t`
            ORDER BY `t`.`label`";
        $params = array('offset' => (int) $offset, 'limit' => (int) $limit, 'entityType' => $entityType);
        if(isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $params = array_merge($params, $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForList($query, $params);
    }

    /**
     * Returns tag list with popularity for provided entity item.
     * 
     * @param integer $entityId
     * @param string $entityType
     * @return array
     */
    public function findEntityTagsWithPopularity( $entityId, $entityType )
    {
        $privacyConditionWhere = '';
        $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('tagEntityTableName' => '`et`', 'entityType' => $entityType, 'listType' => 'tag')));
        if(isset($privacyConditionEvent->getData()['where'])){
            $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
        }
        $query = "SELECT * FROM
	    		(
	    			SELECT `t`.`id` as id, COUNT(*) AS `count`, `t`.`label` AS `label` FROM `" . BOL_EntityTagDao::getInstance()->getTableName() . "` AS `et`
					INNER JOIN `" . $this->getTableName() . "` AS `t`
					ON ( `et`.`tagId` = `t`.`id`)
					WHERE `et`.`entityId` = :entityId AND `et`.`entityType` = :entityType ".$privacyConditionWhere."
					GROUP BY `t`.`id` ORDER BY `count` DESC
				) AS `t` 
				ORDER BY `t`.`label`";
        $params = array('entityId' => $entityId, 'entityType' => $entityType);
        if(isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $params = array_merge($params, $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForList($query, $params);
    }


    /**
     * Delete OW_Base_tags which aren't used anymore
     *
     */
    public function deleteUnusedBaseTags()
    {
        $query = "DELETE FROM ".$this->getTableName()." AS basetag WHERE basetag.id NOT IN(
            SELECT DISTINCT entitytag.tagId FROM ". BOL_EntityTagDao::getInstance()->getTableName() ." AS entitytag )";
        return $this->dbo->delete($query);
    }
}