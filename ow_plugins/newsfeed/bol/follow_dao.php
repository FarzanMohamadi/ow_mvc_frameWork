<?php
/**
 * Data access Object for `newsfeed_follow` table.
 *
 * @package ow_plugins.newsfeed.bol
 * @since 1.0
 */
class NEWSFEED_BOL_FollowDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var NEWSFEED_BOL_FollowDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NEWSFEED_BOL_FollowDao
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
        return 'NEWSFEED_BOL_Follow';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'newsfeed_follow';
    }

    public function addFollow( $userId, $feedType, $feedId, $permission = NEWSFEED_BOL_Service::PRIVACY_EVERYBODY )
    {
        $dto = $this->findFollow($userId, $feedType, $feedId, $permission);

        if ( $dto === null )
        {
            $dto = new NEWSFEED_BOL_Follow();
            $dto->feedType = $feedType;
            $dto->feedId = $feedId;
            $dto->userId = $userId;
            $dto->followTime = time();
        }

        $dto->permission = $permission;
        $this->save($dto);

        return $dto;
    }

    /***
     * @param $userId
     * @return array|mixed|string|null
     */
    public function findFollowersCount( $userId )
    {
        if ( empty($userId) )
        {
            return array();
        }
        $query = "SELECT COUNT(DISTINCT(userId)) AS COUNT FROM " . $this->getTableName() . " WHERE feedType='user' AND feedId=:feedId";
        $params['feedId'] = (int) $userId;

        return $this->dbo->queryForColumn($query, $params);
    }

    /***
     * @param $userId
     * @return array|mixed|string|null
     */
    public function findFollowingCount( $userId )
    {
        if ( empty( $userId) )
        {
            return array();
        }
        $query = "SELECT COUNT(DISTINCT(feedId)) AS COUNT FROM " . $this->getTableName() . " WHERE feedType='user' AND userId=:userId";
        $params['userId'] = (int) $userId;

        return $this->dbo->queryForColumn($query, $params);
    }

    /***
     * @param $userId
     * @param $feedType
     * @param $feedId
     * @param string $permission
     * @return mixed|string|null
     */
    public function findFollow( $userId, $feedType, $feedId, $permission = NEWSFEED_BOL_Service::PRIVACY_EVERYBODY )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('feedId', $feedId);
        $example->andFieldEqual('feedType', $feedType);
        
        if ( !empty($permission) )
        {
            $example->andFieldEqual('permission', $permission);
        }

        return $this->findObjectByExample($example);
    }

    public function findFollows( $userId, $feedType, $feedIds)
    {
        if (!is_array($feedIds) || empty($feedIds)) {
            return array();
        }
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldInArray('feedId', $feedIds);
        $example->andFieldEqual('feedType', $feedType);

        $list = $this->findListByExample($example);
        $groups = array();
        foreach ($list as $item) {
            $groups[$item->feedId] = $item;
        }
        return $groups;
    }

    public function findFollowByFeedList( $userId, $feedList , $permission = NEWSFEED_BOL_Service::PRIVACY_EVERYBODY )
    {
        if ( empty($feedList) )
        {
            return array();
        }

        $where = array();
        foreach ( $feedList as $feed )
        {
            $perm = empty($feed["permission"]) ? $permission : $feed["permission"];
            $permWhere = empty($perm) ? "1" : 'permission="' . $this->dbo->escapeValue($perm) . '"';
            
            $where[] = '(`feedType`="' . $this->dbo->escapeValue($feed["feedType"])
                    . '" AND `feedId`="' . $this->dbo->escapeValue($feed["feedId"])
                    . '" AND ' . $permWhere . ' )';
        }

        $query = "SELECT * FROM " . $this->getTableName() . " WHERE `userId`=:u AND ( " . implode(" OR ", $where) . " )";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            "u" => $userId
        ));
    }

    public function findList( $feedType, $feedId, $permission = null )
    {
        $example = new OW_Example();

        $example->andFieldEqual('feedId', $feedId);
        $example->andFieldEqual('feedType', $feedType);
        
        if ( !empty($permission) )
        {
            $example->andFieldEqual('permission', $permission);
        }

        return $this->findListByExample($example);
    }

    /**
     * In deleting group array of userIds is passed to remove all follow once
     * @param $userId
     * @param $feedType
     * @param $feedId
     * @param null $permission
     * @return int
     */
    public function removeFollow( $userId, $feedType, $feedId, $permission = null )
    {
        $example = new OW_Example();
        if(is_array($userId)) {
            $example->andFieldInArray('userId', $userId);
        }else{
            $example->andFieldEqual('userId', $userId);
        }
        $example->andFieldEqual('feedId', $feedId);
        $example->andFieldEqual('feedType', $feedType);
        
        if ( !empty($permission) )
        {
            $example->andFieldEqual('permission', $permission);
        }

        return $this->deleteByExample($example);
    }

    public function findUserFollowingList($userId)
    {
        $example = new OW_Example();

        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('feedType', 'user');
        return $this->findListByExample($example);
    }

    public function findUserFollowingListWithPaginate($userId, $first, $count)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('feedType', 'user');
        $example->setLimitClause(($first-1)*$count, $count);
        $example->andFieldEqual('permission', 'everybody');
        return $this->findListByExample($example);
    }

    public function findUserFollowerListWithPaginate($userId, $first, $count)
    {
        $example = new OW_Example();
        $example->andFieldEqual('feedType', 'user');
        $example->andFieldEqual('feedId', $userId);
        $example->setLimitClause(($first-1)*$count, $count);
        $example->andFieldEqual('permission', 'everybody');
        return $this->findListByExample($example);
    }

}