<?php
/**
 * Data Access Object for `groups_group_user` table.
 *
 * @package ow_plugins.groups.bol
 * @since 1.0
 */
class GROUPS_BOL_GroupUserDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var GROUPS_BOL_GroupUserDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return GROUPS_BOL_GroupUserDao
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
        return 'GROUPS_BOL_GroupUser';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'groups_group_user';
    }

    public function findListByGroupId( $groupId, $first, $count )
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "userId", array(
            "method" => "GROUPS_BOL_GroupUserDao::findListByGroupId"
        ));
        
        $query = "SELECT u.* FROM " . $this->getTableName() . " u " . $queryParts["join"] 
                . " WHERE " . $queryParts["where"] . " AND u.groupId=:g LIMIT :lf, :lc";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            "g" => $groupId,
            "lf" => $first,
            "lc" => $count
        ));
    }

    public function findListByGroupIdBySearch( $groupId, $first, $count, $searchValue=null)
    {
        $params = array(
            "g" => $groupId,
            "lf" => $first,
            "lc" => $count
        );
        $whereClause='';
        if(isset($searchValue)) {
            $whereClause = ' AND (`user`.username like :searchValue OR (`qd`.questionName = "realname" AND `qd`.textValue like :searchValue)) ';
            $params['searchValue'] = '%' . $searchValue . '%';
        }

        $query = "SELECT u.* FROM " . $this->getTableName() . " u 
        INNER JOIN ". BOL_UserDao::getInstance()->getTableName(). " `user` ON `u`.userId=`user`.id  
        LEFT JOIN ". BOL_QuestionDataDao::getInstance()->getTableName(). " `qd` ON `u`.userId=`qd`.userId "
            . " WHERE u.groupId=:g ".$whereClause." GROUP BY `u`.id LIMIT :lf, :lc";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $params);
    }

    public function findByGroupId( $groupId, $privacy = null )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);

        if ( $privacy !== null )
        {
            $example->andFieldEqual('privacy', $privacy);
        }

        return $this->findListByExample($example);
    }

    public function findByGroupsAndUserId( $groupIds, $userId )
    {
        if (empty($groupIds)) {
            return array();
        }
        $example = new OW_Example();
        $example->andFieldInArray('groupId', $groupIds);
        $example->andFieldEqual('userId', $userId);
        $result = $this->findListByExample($example);
        $data = array();
        foreach ($result as $item) {
            $data[$item->groupId] = $item;
        }
        return $data;
    }


    public function findUserIdsByGroupId( $groupId)
    {

        $query = "SELECT u.userId FROM " . $this->getTableName() ." as u WHERE u.groupId=:g ";

        return $this->dbo->queryForColumnList($query, array(
            "g" => $groupId
        ));
    }


    public function findCountByGroupId( $groupId )
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "userId", array(
            "method" => "GROUPS_BOL_GroupUserDao::findCountByGroupId"
        ));
        
        $query = "SELECT COUNT(DISTINCT u.id) FROM " . $this->getTableName() . " u " . $queryParts["join"] 
                . " WHERE " . $queryParts["where"] . " AND u.groupId=:g";

        return $this->dbo->queryForColumn($query, array(
            "g" => $groupId
        ));
    }

    public function findCountByGroupIdBySearch( $groupId, $searchValue=null )
    {
        $whereClause='';
        $params=array('g' => $groupId);
        if(isset($searchValue)) {
            $whereClause = ' AND (`user`.username like :searchValue OR (`qd`.questionName = "realname" AND `qd`.textValue like :searchValue)) ';
            $params['searchValue'] = '%' . $searchValue . '%';
        }

        $query = "SELECT COUNT(DISTINCT u.id) FROM " . $this->getTableName() . " u 
        INNER JOIN ". BOL_UserDao::getInstance()->getTableName(). " `user` ON `u`.userId=`user`.id  
        LEFT JOIN ". BOL_QuestionDataDao::getInstance()->getTableName(). " `qd` ON `u`.userId=`qd`.userId "
            . " WHERE u.groupId=:g ".$whereClause;

        return $this->dbo->queryForColumn($query, $params);
    }

    public function findCountByGroupIdList( $groupIdList )
    {
        if ( empty($groupIdList) )
        {
            return array();
        }

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "userId", array(
            "method" => "GROUPS_BOL_GroupUserDao::findCountByGroupIdList"
        ));
        
        $query = 'SELECT u.groupId, COUNT(*) count FROM ' . $this->getTableName() . ' u '
                . $queryParts["join"]
                . ' WHERE ' . $queryParts["where"] . ' AND u.groupId IN (' . implode(',', $groupIdList) . ') GROUP BY u.groupId';

        $list = $this->dbo->queryForList($query, null
                , GROUPS_BOL_GroupDao::LIST_CACHE_LIFETIME, array(GROUPS_BOL_GroupDao::LIST_CACHE_TAG));

        $resultList = array();
        foreach ( $list as $item )
        {
            $resultList[$item['groupId']] = $item['count'];
        }

        foreach ( $groupIdList as $groupId )
        {
            $resultList[$groupId] = empty($resultList[$groupId]) ? 0 : $resultList[$groupId];
        }

        return $resultList;
    }

    /**
     * 
     * @param int $groupId
     * @param int $userId
     * @return GROUPS_BOL_GroupUser
     */
    public function findGroupUser( $groupId, $userId )
    {
        if ($userId == null || $groupId == null) {
            return null;
        }
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldEqual('userId', $userId);

        return $this->findObjectByExample($example);
    }

    /**
     *
     * @param array $userIds
     * @return array
     */
    public function findGroupsByUserIds( $userIds )
    {
        if ($userIds == null || empty($userIds)) {
            return array();
        }
        $example = new OW_Example();
        $example->andFieldInArray('userId', $userIds);

        return $this->findListByExample($example);
    }

    /**
     * @param $groupId
     * @return array
     */
    public function findGroupUsers( $groupId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        return $this->findIdListByExample($example);
    }

    /**
     * @param $groupId
     * @param $userIds
     * @return array
     */
    public function findGroupUserIdsByGroupIdAndUserIds( $groupId, $userIds)
    {
        if (empty($userIds)) {
            return [];
        }
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldInArray('userId', $userIds);
        return $this->findIdListByExample($example);
    }

    public function deleteByGroupId( $groupId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);

        return $this->deleteByExample($example);
    }

    public function deleteByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->deleteByExample($example);
    }

    public function deleteByGroupAndUserId( $groupId, $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldEqual('userId', $userId);

        return $this->deleteByExample($example);
    }

    public function setPrivacy( $userId, $privacy )
    {
        $query = 'UPDATE ' . $this->getTableName() . ' SET privacy=:p WHERE userId=:u';

        $this->dbo->query($query, array(
            'p' => $privacy,
            'u' => $userId
        ));
    }
}