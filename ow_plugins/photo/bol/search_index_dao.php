<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.photo.bol
 * @since 1.6.1
 */
class PHOTO_BOL_SearchIndexDao extends OW_BaseDao
{
    CONST ENTITY_TYPE_ID = 'entityTypeId';
    CONST ENTITY_ID = 'entityId';
    CONST CONTENT = 'content';
    CONST ENTITY_TYPE = 'entityType';
    CONST ACTIVE = 'active';
    CONST COMMENT_ENTITY_ID = 'commentEntityId';
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'photo_search_index';
    }
    
    public function getDtoClassName()
    {
        return 'PHOTO_BOL_SearchIndex';
    }
    
    public function getMinWordLen()
    {
        static $ftMinWordLen = NULL;
        
        if ( $ftMinWordLen === NULL )
        {
            $len = $this->dbo->queryForRow('SHOW VARIABLES LIKE "ft_min_word_len"');
            $ftMinWordLen = (int)$len['Value'];
        }
        
        return $ftMinWordLen;
    }
    
    public function findIndexedData( $searchVal, array $entityTypes = array(), $limit = PHOTO_BOL_SearchService::SEARCH_LIMIT )
    {
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByDesc', array('photo' => 'p', 'album' => 'a'));
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = 'SELECT `index`.*
            FROM `' . $this->getTableName() . '` AS `index`
                INNER JOIN `' . PHOTO_BOL_PhotoDao::getInstance()->getTableName() . '` AS `p` ON(`index`.`entityId` = `p`.`id`)
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
            ' . $condition['join'] . '
            WHERE `index`.`' . self::CONTENT . '` like \'%'.$this->dbo->escapeValue($searchVal).'%\' AND `p`.`status` = :status AND ' . $condition['where'] . $privacyConditionWhere;
        
        if ( count($entityTypes) !== 0 )
        {
            $sql .= ' AND `index`.`' . self::ENTITY_TYPE_ID . '` IN (SELECT `entity`.`id`
                FROM `' . PHOTO_BOL_SearchEntityTypeDao::getInstance()->getTableName() . '` AS `entity`
                WHERE `entity`.`' . PHOTO_BOL_SearchEntityTypeDao::ENTITY_TYPE . '` IN( ' . $this->dbo->mergeInClause($entityTypes) . '))';
        }
        
        $sql .= ' LIMIT :limit';
        $params = array_merge($condition['params'], array('limit' => (int)$limit, 'status' => 'approved'));
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $params = array_merge($params, $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), $params);
    }

    public function findIndexedPhotos( $searchVal, $listType,  array $entityTypes = array(), $limit = PHOTO_BOL_SearchService::SEARCH_LIMIT )
    {
        $list_type_query = PHOTO_BOL_SearchIndexDao::get_list_type_query($listType, $entityTypes);
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByDesc', array('photo' => 'p', 'album' => 'a'));
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = 'SELECT DISTINCT `index`.*
            FROM `' . $this->getTableName() . '` AS `index`';

        if (in_array(PHOTO_BOL_SearchService::ENTITY_TYPE_PHOTO, $entityTypes)) {
            $sql .= 'INNER JOIN `' . PHOTO_BOL_PhotoDao::getInstance()->getTableName() . '` AS `p` ON(`index`.`entityId` = `p`.`id`)
                    INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)';
        }else{
            $sql .= 'INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`index`.`entityId` = `a`.`id`)
                    INNER JOIN `' . PHOTO_BOL_PhotoDao::getInstance()->getTableName() . '` AS `p` ON(`a`.`id` = `p`.`albumId`)';
        }
        $sql .= $condition['join'] . '
            WHERE `index`.`' . self::CONTENT . '` like \'%'.$this->dbo->escapeValue($searchVal).'%\' AND `p`.`status` = :status AND ' . $condition['where'] . $privacyConditionWhere;

        if ( count($entityTypes) !== 0 )
        {
            $sql .= ' AND `index`.`' . self::ENTITY_TYPE_ID . '` IN (SELECT `entity`.`id`
                FROM `' . PHOTO_BOL_SearchEntityTypeDao::getInstance()->getTableName() . '` AS `entity`
                WHERE `entity`.`' . PHOTO_BOL_SearchEntityTypeDao::ENTITY_TYPE . '` IN( ' . $this->dbo->mergeInClause($entityTypes) . '))';
        }

        if  ($list_type_query != null)
            $sql.= ' AND `index`.`entityID` IN (' . $list_type_query .') ';

        if ($listType == "latest")
            $sql .= ' ORDER BY `index`.`entityID` DESC';

        //omit limit for search result
        //$sql .= ' LIMIT :limit';
        $params = array_merge($condition['params'], array('status' => 'approved'));//'limit' => (int)$limit,
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $params = array_merge($params, $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), $params);
    }

    public function get_list_type_query($listType, $entityTypes = array(PHOTO_BOL_SearchService::ENTITY_TYPE_PHOTO) ){
        switch ($listType){
            case "latest":
                if (in_array(PHOTO_BOL_SearchService::ENTITY_TYPE_PHOTO, $entityTypes)){
                    $query = 'SELECT `p`.`id`
                       FROM `'. PHOTO_BOL_PhotoDao::getInstance()->getTableName() .'` AS `p`
                       INNER JOIN `'. PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() .'` AS `a` ON (`p`.`albumId` = `a`.`id`)                        
                       WHERE `a`.`entityType` = "user" AND `p`.`status` = "approved"';
                }else{
                    $query = 'SELECT `pa`.`id`
                       FROM `'. PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() .'` AS `pa`                       
                       WHERE `pa`.`entityType` = "user"';
                }
                break;

            case "toprated":
                $queryParts = BOL_ContentService::getInstance()->getQueryFilter(array(
                    BASE_CLASS_QueryBuilderEvent::TABLE_USER => 'r',
                    BASE_CLASS_QueryBuilderEvent::TABLE_CONTENT => 'r'
                ), array(
                    BASE_CLASS_QueryBuilderEvent::FIELD_USER_ID => 'userId',
                    BASE_CLASS_QueryBuilderEvent::FIELD_CONTENT_ID => 'id'
                ), array(
                    BASE_CLASS_QueryBuilderEvent::OPTION_METHOD => __METHOD__,
                    BASE_CLASS_QueryBuilderEvent::OPTION_TYPE => 'photo_rates'
                ));

                $privacyConditionWhere = '';
                $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('entityType' => 'photo_rates', 'rateTableName' => '`r`', 'listType' => 'rateDao')));
                if(isset($privacyConditionEvent->getData()['where'])){
                    $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
                }

                $query = 'SELECT `r`.`' . self::ENTITY_ID . '` AS `id`
            FROM `' .  BOL_RateDao::getInstance()->getTableName() . '` AS `r`
            ' . $queryParts['join'] . '
            WHERE `r`.`' . self::ENTITY_TYPE . '` = :entityType AND `r`.`' . self::ACTIVE . '` = 1 ' . ' AND ' . $queryParts['where'] . $privacyConditionWhere . '
            GROUP BY `r`.`' . self::ENTITY_ID . '`
            ORDER BY  AVG(`r`.`score`) DESC, COUNT(*) DESC, MAX(`r`.`timeStamp`)';
                $boundParams = array_merge(array('entityType' => 'photo_rates'), $queryParts['params']);
                if(isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
                    $boundParams = array_merge($boundParams, $privacyConditionEvent->getData()['params']);
                }

                foreach ($boundParams as $key=>$value)
                    $query = str_replace(":". $key, '"' . $value . '"', $query);
                break;

            case "most_discussed":
                $queryParts = BOL_ContentService::getInstance()->getQueryFilter(array(
                    BASE_CLASS_QueryBuilderEvent::TABLE_USER => 'c',
                    BASE_CLASS_QueryBuilderEvent::TABLE_CONTENT => 'c',
                    'comment_entity' => 'ce'
                ), array(
                    BASE_CLASS_QueryBuilderEvent::FIELD_USER_ID => 'userId',
                    BASE_CLASS_QueryBuilderEvent::FIELD_CONTENT_ID => 'id'
                ), array(
                    BASE_CLASS_QueryBuilderEvent::OPTION_METHOD => __METHOD__,
                    BASE_CLASS_QueryBuilderEvent::OPTION_TYPE => 'photo_comments'
                ));

                $privacyConditionWhere = '';
                $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('entityType' => 'photo_comments', 'commentEntityTableName' => '`ce`', 'listType' => 'commentDao')));
                if(isset($privacyConditionEvent->getData()['where'])){
                    $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
                }

                $query = 'SELECT `ce`.`entityId` AS `id`
            FROM `' . BOL_CommentDao::getInstance()->getTableName() . '` AS `c`
			    LEFT JOIN `' . BOL_CommentEntityDao::getInstance()->getTableName() . '` AS `ce` ON (`c`.`' . self::COMMENT_ENTITY_ID . '` = `ce`.`id`)
			    ' . $queryParts['join'] . '
			WHERE `ce`.`' . BOL_CommentEntityDao::ENTITY_TYPE . '` = :entityType AND `ce`.`' . BOL_CommentEntityDao::ACTIVE . '` = 1 AND ' . $queryParts['where'] . $privacyConditionWhere .  '
			GROUP BY `ce`.`' . BOL_CommentEntityDao::ENTITY_ID . '`
			ORDER BY COUNT(*) DESC, `id` DESC';
                $boundParams = array_merge(array('entityType' => 'photo_comments'), $queryParts['params']);
                if(isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
                    $boundParams = array_merge($boundParams, $privacyConditionEvent->getData()['params']);
                }

                foreach ($boundParams as $key=>$value){
                    $query = str_replace(":". $key, '"' . $value . '"', $query);;
                }
                break;
            default:
                return null;
        }
        return $query;
    }

    public function deleteIndexItem( $entityTypeId, $entityId )
    {
        if ( empty($entityTypeId) || empty($entityId) )
        {
            return FALSE;
        }
        
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_TYPE_ID, $entityTypeId);
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        
        return $this->deleteByExample($example);
    }
}
