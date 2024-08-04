<?php
/**
 * Data Access Object for `event_item` table.
 *
 * @package ow_plugins.event.bol
 * @since 1.0
 */
class EVENT_BOL_EventUserDao extends OW_BaseDao
{
    const EVENT_ID = 'eventId';
    const USER_ID = 'userId';
    const TIME_STAMP = 'timeStamp';
    const STATUS = 'status';

    const VALUE_STATUS_YES = 1;
    const VALUE_STATUS_MAYBE = 2;
    const VALUE_STATUS_NO = 3;

    const CACHE_TAG_EVENT_USER_LIST = 'event_users_list_event_id_';

    const CACHE_LIFE_TIME = 86400; //24 hour

    /**
     * Singleton instance.
     *
     * @var EVENT_BOL_EventUserDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return EVENT_BOL_EventUserDao
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
        return 'EVENT_BOL_EventUser';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'event_user';
    }

    public function deleteByEventId( $id )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::EVENT_ID, (int) $id);

        $this->deleteByExample($example);
    }

    public function findListByEventIdAndStatus( $eventId, $status, $first, $count )
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("e", "userId", array(
            "method" => "EVENT_BOL_EventUserDao::findListByEventIdAndStatus"
        ));

        $query = " SELECT e.* FROM  " . $this->getTableName() . " e
                    " . $queryParts['join'] . "
                    WHERE " . $queryParts['where'] . "  AND e.`".self::EVENT_ID."` = :eventId AND e.`" . self::STATUS . "` = :status
                    LIMIT :first, :count " ;

        return $this->dbo->queryForObjectList( $query, $this->getDtoClassName(), array( 'eventId' => (int)$eventId, 'status' => (int)$status, 'first' => $first, 'count' => $count ) );
    }

    public function findListByEventId( $eventId )
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("e", "userId", array(
            "method" => "EVENT_BOL_EventUserDao::findListByEventId"
        ));

        $query = " SELECT e.* FROM  " . $this->getTableName() . " e
                    " . $queryParts['join'] . "
                    WHERE " . $queryParts['where'] . "  AND e.`".self::EVENT_ID."` = :eventId" ;

        return $this->dbo->queryForObjectList( $query, $this->getDtoClassName(), array( 'eventId' => (int)$eventId) );
    }

    public function findUsersCountByEventIdAndStatus( $eventId, $status )
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("e", "userId", array(
            "method" => "EVENT_BOL_EventUserDao::findUsersCountByEventIdAndStatus"
        ));

        $query = " SELECT count(e.id) FROM  " . $this->getTableName() . " e
                    " . $queryParts['join'] . "
                    WHERE " . $queryParts['where'] . " AND e.`".self::EVENT_ID."` = :eventId AND e.`" . self::STATUS . "` = :status ";

        return $this->dbo->queryForColumn( $query, array( 'eventId' => (int)$eventId, 'status' => (int)$status ), self::CACHE_LIFE_TIME, array(self::CACHE_TAG_EVENT_USER_LIST . $eventId) );
    }

    /**
     * @param integer $eventId
     * @param integer $userId
     * @return EVENT_BOL_EventUser
     */
    public function findObjectByEventIdAndUserId( $eventId, $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::EVENT_ID, (int) $eventId);
        $example->andFieldEqual(self::USER_ID, (int) $userId);

        return $this->findObjectByExample($example);
    }

    /**
     * @param integer $userId
     * @param integer $first
     * @param integer $count
     * @return array
     */
    public function findByUserId( $userId, $first, $count )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);
        $example->setLimitClause($first, $count);

        return $this->findListByExample($example);
    }

    public function findUsersCountByEventIdListAndStatus( $eventIdList, $status )
    {
        if ( empty($eventIdList) )
        {
            return array();
        }

        $query = " SELECT  e.`".self::EVENT_ID."`, count(e." . self::STATUS . ") as usersCount FROM  " . $this->getTableName() . " e                 
                    WHERE  e.`".self::EVENT_ID."`  IN (" . implode(",", array_map("intval", array_unique($eventIdList))) . ") AND e.`" . self::STATUS . "` = :status GROUP BY  e.`".self::EVENT_ID."`";
        $list = $this->dbo->queryForList($query, array('status' => $status));
        $resultList = array();
        foreach ( $list as $item )
        {
            $resultList[$item['eventId']] = $item['usersCount'];
        }

        return $resultList;

    }

    public function findUsersCountByEventIdList( $eventIdList )
    {
        if ( empty($eventIdList) )
        {
            return array();
        }

        $query = " SELECT  e.`".self::EVENT_ID."`, count(e." . self::STATUS . ") as usersCount FROM  " . $this->getTableName() . " e                 
                    WHERE  e.`".self::EVENT_ID."`  IN (" . implode(",", array_map("intval", array_unique($eventIdList))) . ") GROUP BY  e.`".self::EVENT_ID. "`";
        $list = $this->dbo->queryForList($query);
        $resultList = array();
        foreach ( $list as $item )
        {
            $resultList[$item['eventId']] = $item['usersCount'];
        }

        return $resultList;

    }
}
