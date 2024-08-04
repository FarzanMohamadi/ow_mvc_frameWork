<?php
/**
 * Data Access Object for `photo_album` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.bol
 * @since 1.0
 */
class PHOTO_BOL_PhotoAlbumDao extends OW_BaseDao
{
    CONST NAME = 'name';
    CONST USER_ID = 'userId';
    CONST CREATE_DATETIME = 'createDatetime';

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var PHOTO_BOL_PhotoAlbumDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PHOTO_BOL_PhotoAlbumDao
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
        return 'PHOTO_BOL_PhotoAlbum';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'photo_album';
    }

    /**
     * Count albums added by a user
     *
     * @param int $userId
     * @param $exclude
     * @return int
     */
    public function countAlbums( $userId, $exclude, $excludeEmpty = false )
    {
        if ( !$userId )
        {
            return false;
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('countAlbums',
            array('album' => 'a', 'photo' => 'p'),
            array(
                'userId' => $userId,
                'exclude' => $exclude,
                'excludeEmpty' => $excludeEmpty
            )
        );
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => '`p`', 'listType' => 'latest', 'objectType' => 'photo', 'privacyTableNameExist' => $excludeEmpty, 'object_list' => 'album', 'albumOwnerId' => $userId)));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }

        $sql = 'SELECT COUNT(DISTINCT `a`.`id`)
            FROM `' . $this->getTableName() . '` AS `a`
                ' . $condition['join'] . '
                ' . ($excludeEmpty ? 'INNER JOIN `' . PHOTO_BOL_PhotoDao::getInstance()->getTableName() . '` AS `p` ON(`a`.`id` = `p`.`albumId`)' : '') . '
            WHERE `a`.`userId` = :userId AND
            ' . $condition['where'] . ' AND
            ' . (!empty($exclude) ? '`a`.`id` NOT IN(' . $this->dbo->mergeInClause($exclude) . ')' : '1') . $privacyConditionWhere;
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $condition['params'] = array_merge($condition['params'], $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForColumn($sql, array_merge(
            array('userId' => $userId),
            $condition['params']
        ));
    }
    
     /**
     * Count albums for entity
     *
     * @param $entityId
     * @param $entityType
     * @return int
     */
    public function countEntityAlbums( $entityId, $entityType )
    {
        if ( !$entityId || !mb_strlen($entityType) )
        {
            return false;
        }

        $example = new OW_Example();
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);

        return $this->countByExample($example);
    }

    /**
     * Get the list of user albums
     *
     * @param int $userId
     * @param int $page
     * @param int $limit
     * @param $exclude
     * @return array of PHOTO_BOL_PhotoAlbum
     */
    public function getUserAlbumList( $userId, $page, $limit, $exclude )
    {
        $first = ( $page - 1 ) * $limit;

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('getUserAlbumList',
            array(
                'album' => 'a',
                'photo' => 'p'
            ),
            array(
                'userId' => $userId,
                'page' => $page,
                'limit' => $limit,
                'exclude' => $exclude
            )
        );
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = 'SELECT `a`.*
            FROM `%s` AS `a`
                INNER JOIN `%s` AS `p`
                    ON(`p`.`albumId` = `a`.`id`)
                %s
            WHERE `a`.`userId` = :userId AND
                %s AND
                %s '.$privacyConditionWhere.'
            GROUP BY `a`.`id`
            LIMIT :first, :limit';
        $sql = sprintf($sql,
            $this->getTableName(),
            PHOTO_BOL_PhotoDao::getInstance()->getTableName(),
            $condition['join'],
            $condition['where'],
            !empty($exclude) ? '`a`.`id` NOT IN(' . $this->dbo->mergeInClause($exclude) . ')' : '1'
        );
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $condition['params'] = array_merge($condition['params'], $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array_merge(
            array(
                'userId' => $userId,
                'first' => (int) $first,
                'limit' => (int) $limit
            ),
            $condition['params']
        ));
    }
    
    /**
     * Get album list for user
     *
     * @param int $userId
     * @param int $first
     * @param int $limit
     * @param array of int $exclude
     * @return array of PHOTO_BOL_PhotoAlbum
     */
    public function findUserAlbumList( $userId, $first, $limit, array $exclude = array() )
    {
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('findUserAlbumList',
            array(
                'album' => 'a',
                'photo' => 'p'
            ),
            array(
                'userId' => $userId,
                'first' => $first,
                'limit' => $limit,
                'exclude' => $exclude
            )
        );
        $isOwner = ($userId == OW::getUser()->getId() || OW::getUser()->isAuthorized('photo'));
        $privacyConditionWhere = '';
        if(!$isOwner) {
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if (isset($privacyConditionEvent->getData()['where'])) {
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = 'SELECT `a`.*
            FROM `' . $this->getTableName() . '` AS `a`
                INNER JOIN `' . PHOTO_BOL_PhotoDao::getInstance()->getTableName() . '` AS `p` ON(`p`.`albumId` = `a`.`id` AND `p`.`status` = :status)
                ' . $condition['join'] . '
            WHERE `a`.`' . self::USER_ID . '` = :userId ' .
                (count($exclude) !== 0 ? ' AND `a`.`id` NOT IN (' . implode(',', array_map('intval', $exclude)) . ')' : '') . ' AND
                ' . $condition['where'] . $privacyConditionWhere . '
            GROUP BY `a`.`id`
            ORDER BY `a`.`id` DESC
            LIMIT :first, :limit';

        $params = array('userId' => $userId, 'status' => PHOTO_BOL_PhotoDao::STATUS_APPROVED, 'first' => (int)$first, 'limit' => (int)$limit);
        if($privacyConditionWhere!='' && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $params = array_merge($params, $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForList($sql, array_merge($params, $condition['params']));
    }
    
    /**
     * Get album list for user friends
     *
     * @param int $userId
     * @param int $first
     * @param int $limit
     * @param array of int $exclude
     * @return array of PHOTO_BOL_PhotoAlbum
     */
    public function findUserFriendsAlbumList( $userId, $first, $limit, array $exclude = array() )
    {
        $sql = 'SELECT distinct `a`.*
            FROM `' . $this->getTableName() . '` AS `a`
            INNER JOIN `' . PHOTO_BOL_PhotoDao::getInstance()->getTableName() . '` AS `p` ON(`p`.`albumId` = `a`.`id` AND `p`.`status` = :status)
            INNER JOIN `' . FRIENDS_BOL_FriendshipDao::getInstance()->getTableName() . '` AS `f` ON ( `a`.`userId` = `f`.`userId` OR `a`.`userId` = `f`.`friendId` ) AND ( (`f`.`userId` = :userId OR `f`.`friendId` = :userId) AND `f`.`status` = :active)
            WHERE ( `p`.`privacy` = :friends_only
            OR `p`.`privacy` = :everybody )
            '. (count($exclude) !== 0 ? ' AND `a`.`id` NOT IN (' . implode(',', array_map('intval', $exclude)) . ')' : '') . '
            AND
            `a`.`userId` <> :userId
            GROUP BY `a`.`id`
            ORDER BY `a`.`id` DESC
            LIMIT :first, :limit';
        $params = array('active' => FRIENDS_BOL_FriendshipDao::VAL_STATUS_ACTIVE, 'friends_only' => 'friends_only','everybody' => 'everybody', 'userId' => $userId, 'status' => PHOTO_BOL_PhotoDao::STATUS_APPROVED, 'first' => (int)$first, 'limit' => (int)$limit);
        return $this->dbo->queryForList($sql, $params);
    }

    /**
     * Get album list for entity
     *
     * @param $entityId
     * @param $entityType
     * @param int $page
     * @param int $limit
     * @return array of PHOTO_BOL_PhotoAlbum
     */
    public function getEntityAlbumList( $entityId, $entityType, $page, $limit )
    {
        $first = ( $page - 1 ) * $limit;

        $example = new OW_Example();
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);
        $example->setLimitClause($first, $limit);

        return $this->findListByExample($example);
    }

    public function getUserAlbums( $userId, $offset, $limit )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->setLimitClause($offset, $limit);
        $example->setOrder('`id` DESC');

        return $this->findListByExample($example);
    }

    public function getEntityAlbums( $entityId, $entityType, $offset, $limit )
    {
        $example = new OW_Example();
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);
        $example->setLimitClause($offset, $limit);

        return $this->findListByExample($example);
    }
    
    public function getUserAlbumIdList( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findIdListByExample($example);
    }
  
    public function getEntityAlbumIdList( $entityId, $entityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);

        return $this->findIdListByExample($example);
    }

    /**
     * Finds Photo album by album name
     *
     * @param string $name
     * @param int $userId
     * @return PHOTO_BOL_PhotoAlbum
     */
    public function findAlbumByName( $name, $userId )
    {
        $name = trim($name);

        $userId = (int) $userId;

        $example = new OW_Example();
        $example->andFieldEqual('name', $name);
        $example->andFieldEqual('userId', $userId);
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }
    
    /**
     * Finds entity photo album by album name
     *
     * @param string $name
     * @param $entityId
     * @param $entityType
     * @return PHOTO_BOL_PhotoAlbum
     */
    public function findEntityAlbumByName( $name, $entityId, $entityType )
    {
        $name = trim($name);

        $entityId = (int) $entityId;

        $example = new OW_Example();
        $example->andFieldEqual('name', $name);
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }
    
    /**
     * Get user albums for suggest field
     *
     * @param int $userId
     * @param string $query
     * @return array of PHOTO_Bol_PhotoAlbum
     */
    public function suggestUserAlbums( $userId, $query )
    {
        if ( !$userId )
            return false;

        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->setOrder('`name` ASC');

        if ( strlen($query) )
            $example->andFieldLike('name', $query . '%');

        $example->setLimitClause(0, 10);

        return $this->findListByExample($example);
    }
    
    /**
     * Get entity albums for suggest field
     *
     * @param string $entityType
     * @param int $entityId
     * @param string $query
     * @return array of PHOTO_Bol_PhotoAlbum
     */
    public function suggestEntityAlbums( $entityType, $entityId, $query )
    {
        if ( !$entityId )
        {
            return false;
        }

        $example = new OW_Example();
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);
        $example->setOrder('`name` ASC');

        if ( strlen($query) )
            $example->andFieldLike('name', $query . '%');

        $example->setLimitClause(0, 10);

        return $this->findListByExample($example);
    }


    /**
     * @param $offset
     * @param $limit
     * @return array
     */
    public function findLastAlbums( $offset, $limit )
    {
        $query = 'SELECT * FROM '.$this->getTableName().' ORDER BY createDatetime DESC LIMIT ' . abs( (int) $offset ) . ', ' . abs( (int) $limit );
        return $this->dbo->queryForList($query);
    }


    /**
     * Find last albums ids
     *
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function findLastAlbumsIds( $offset, $limit )
    {
        $example = new OW_Example();
        $example->setOrder('createDatetime DESC');
        $example->setLimitClause((int) $offset, (int) $limit);

        return $this->findIdListByExample($example);
    }

    /**
     * Find latest albums authors ids
     *
     * @param integer $first
     * @param integer $count
     * @return array
     */
    public function findLatestAlbumsAuthorsIds($first, $count)
    {
        $sql = 'SELECT `' . self::USER_ID . '`
            FROM `' . $this->getTableName() . '`
            GROUP BY `' . self::USER_ID . '`, `'.self::CREATE_DATETIME.'` ORDER BY `'.self::CREATE_DATETIME.'` DESC LIMIT :f, :c';

        return $this->dbo->queryForColumnList($sql, array(
            'f' => (int) $first,
            'c' => (int) $count
        ));
    }

    /**
     * Get albums to be deleted
     *
     * @param int $limit
     * @return array
     */
    public function getAlbumsForDelete( $limit )
    {
        $example = new OW_Example();
        $example->setOrder('createDatetime ASC');
        $example->setLimitClause(0, (int)$limit);

        return $this->findIdListByExample($example);
    }

    /**
     * @param $albumIds
     * @return array
     */
    public function findAlbumsByIdList( $albumIds )
    {
        if (empty($albumIds)) {
            return array();
        }
        $result = $this->findByIdList($albumIds);
        $data = array();
        foreach ($result as $item) {
            $data[$item->id] = $item;
        }
        return $data;
    }
    
    public function findAlbumNameListByIdList( array $idList )
    {
        if ( count($idList) === 0 )
        {
            return array();
        }
        
        $sql = 'SELECT `id`, `' . self::NAME . '`, `' . self::USER_ID . '`
            FROM `' . $this->getTableName() . '`
            WHERE `id` IN (' . implode(',', array_map('intval', array_unique($idList))) . ')';
        
        $result = array();
        $resource = $this->dbo->queryForList($sql);
        
        foreach ( $resource as $row )
        {
            $result[$row['id']] = array('name' => $row[self::NAME], 'userId' => $row[self::USER_ID]);
        }
        
        return $result;
    }
    
    public function isAlbumOwner( $albumId, $userId )
    {
        if ( empty($albumId) || empty($userId) )
        {
            return FALSE;
        }
        
        $sql = 'SELECT COUNT(*)
            FROM `' . $this->getTableName() . '`
            WHERE `id` = :albumId AND `userId` = :userId';
        
        return (int)$this->dbo->queryForColumn($sql, array('albumId' => $albumId, 'userId' => $userId)) > 0;
    }
    
    public function findAlbumNameListByUserId( $userId, $excludeIdList = array() )
    {
        if ( empty($userId) )
        {
            return array();
        }
        
        $sql = 'SELECT `id`, `' . self::NAME . '`
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::USER_ID . '` = :userId' . (count($excludeIdList) !== 0 ? ' AND `id` NOT IN (' . implode(',', array_map('intval', array_unique($excludeIdList))) . ')' : '') . '
            ORDER BY `' . self::NAME . '`';

        $result = array();
        $rows = $this->dbo->queryForList($sql, array('userId' => $userId));
        
        foreach ( $rows as $row )
        {
            $result[$row['id']] = $row[self::NAME];
        }

        return $result;
    }

    public function findAlbumsAuthorIds($albumIds){
        if ( empty($albumIds) )
        {
            return array();
        }

        $sql = 'SELECT `id`, `' . self::USER_ID . '`
            FROM `' . $this->getTableName() . '`
            WHERE `id` IN (' . implode(',', array_map('intval', array_unique($albumIds))) . ')';

        $list = $this->dbo->queryForList($sql);
        $resultList = array();
        foreach ( $list as $item )
        {
            $resultList[$item['id']] = $item['userId'];
        }

        return $resultList;
    }

    public function findAlbumsAuthorIdsList($albumIds, $first, $limit){

        if ( empty($albumIds) )
        {
            return array();
        }

        $sql = 'SELECT `id`, `' . self::USER_ID . '`
            FROM `' . $this->getTableName() . '`
            WHERE `id` IN (' . implode(',', array_map('intval', array_unique($albumIds))) . ')
            ORDER BY `id` DESC
            LIMIT :first, :limit';
        $params = array('first' => (int)$first, 'limit' => (int)$limit);
        $list = $this->dbo->queryForList($sql,$params);

        $resultList = array();
        foreach ( $list as $key => $item )
        {
            $resultList[$key]['albumId'] = $item['id'];
            $resultList[$key]['userId'] = $item['userId'];
        }

        return $resultList;
    }
}
