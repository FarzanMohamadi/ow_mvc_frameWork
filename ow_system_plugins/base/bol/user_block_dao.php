<?php
/**
 * Data Access Object for `user_block` table.  
 * 
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_UserBlockDao extends OW_BaseDao
{
    const USER_ID = 'userId';
    const BLOCKED_USER_ID = 'blockedUserId';
    const CACHE_TAG_BLOCKED_USER = 'base.blocked_user';
    const CACHE_LIFE_TIME = 86400; //24 hour

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
     * @var BOL_UserBlockDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_UserBlockDao
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
        return 'BOL_UserBlock';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_user_block';
    }

    public function findBlockedUserList($userId, $first, $count)
    {
        $queryParts = BOL_UserService::getInstance()->getQueryFilter(array(
            BASE_CLASS_QueryBuilderEvent::TABLE_USER => 'u'
        ), array(
            BASE_CLASS_QueryBuilderEvent::FIELD_USER_ID => 'blockedUserId'
        ), array(
            BASE_CLASS_QueryBuilderEvent::OPTION_METHOD => __METHOD__
        ));

        $query = "SELECT u.* FROM " . $this->getTableName() . " u " . $queryParts["join"]
            . " WHERE " . $queryParts["where"] . " AND u.userId=:userId  LIMIT :lf, :lc";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            "userId" => $userId,
            "lf" => $first,
            "lc" => $count
        ));
    }

    /**
     * @param $userId
     * @param $blockedUserId
     * @return mixed
     */
    public function findBlockedUser( $userId, $blockedUserId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);
        $example->andFieldEqual(self::BLOCKED_USER_ID, (int) $blockedUserId);

        return $this->findObjectByExample($example, BOL_UserBlockDao::CACHE_LIFE_TIME, array(BOL_UserBlockDao::CACHE_TAG_BLOCKED_USER));
    }

    public function findBlockedList( $userId, $userIdList )
    {
        if (!is_array($userIdList) || empty($userIdList)) {
            return array();
        }
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);
        $example->andFieldInArray(self::BLOCKED_USER_ID, $userIdList);

        return $this->findListByExample($example, BOL_UserBlockDao::CACHE_LIFE_TIME, array(BOL_UserBlockDao::CACHE_TAG_BLOCKED_USER));
    }

    public function findBlockedByList( $userId, $userIdList )
    {
        if (!is_array($userIdList) || empty($userIdList)) {
            return array();
        }
        $example = new OW_Example();
        $example->andFieldInArray(self::USER_ID, $userIdList);
        $example->andFieldEqual(self::BLOCKED_USER_ID, (int) $userId);

        return $this->findListByExample($example, BOL_UserBlockDao::CACHE_LIFE_TIME, array(BOL_UserBlockDao::CACHE_TAG_BLOCKED_USER));
    }

    public function deleteBlockedUser( $userId, $blockedUserId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);
        $example->andFieldEqual(self::BLOCKED_USER_ID, (int) $blockedUserId);

        $this->deleteByExample($example);
    }

    public function countBlockedUsers( $userId )
    {
        $queryParts = BOL_UserService::getInstance()->getQueryFilter(array(
            BASE_CLASS_QueryBuilderEvent::TABLE_USER => 'u'
        ), array(
            BASE_CLASS_QueryBuilderEvent::FIELD_USER_ID => 'blockedUserId'
        ), array(
            BASE_CLASS_QueryBuilderEvent::OPTION_METHOD => __METHOD__
        ));

        $query = "SELECT COUNT(DISTINCT u.blockedUserId) FROM " . $this->getTableName() . " u " . $queryParts["join"]
            . " WHERE " . $queryParts["where"] . " AND u.userId=:userId";

        return $this->dbo->queryForColumn($query, array(
            "userId" => $userId
        ));
    }

    public function deleteByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);
        $this->deleteByExample($example);
        
        $example = new OW_Example();
        $example->andFieldEqual(self::BLOCKED_USER_ID, (int) $userId);
        $this->deleteByExample($example);
    }

    protected function clearCache()
    {
        OW::getCacheManager()->clean(array(BOL_UserBlockDao::CACHE_TAG_BLOCKED_USER));
    }
}