<?php
/**
 * Data Access Object for `notifications_rule` table.
 *
 * @package ow_plugins.notifications.bol
 * @since 1.0
 */
class NOTIFICATIONS_BOL_SendQueueDao extends OW_BaseDao
{
    /**sendQueueDao
     * Singleton instance.
     *
     * @var NOTIFICATIONS_BOL_SendQueueDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NOTIFICATIONS_BOL_SendQueueDao
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
        return 'NOTIFICATIONS_BOL_SendQueue';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'notifications_send_queue';
    }

    public function fillData( $period, $defaultSchedules )
    {
        $usersDao = BOL_UserDao::getInstance();
        $scheduleDao = NOTIFICATIONS_BOL_ScheduleDao::getInstance();

        $query = "REPLACE INTO " . $this->getTableName() . " (`userId`, `timeStamp`) SELECT u.id, UNIX_TIMESTAMP() FROM " . $usersDao->getTableName() . " u
                    LEFT JOIN " . $scheduleDao->getTableName() . " s ON u.id = s.userId
                    WHERE (IF( s.schedule IS NULL, :ds, s.schedule )=:as  AND u.activityStamp < :activityStamp ) OR IF( s.schedule IS NULL, :ds, s.schedule )=:is GROUP By  u.id ORDER BY u.activityStamp DESC ";

        return $this->dbo->query($query, array(
            'activityStamp' => time() - $period,
            'ds' => $defaultSchedules,
            'is' => NOTIFICATIONS_BOL_Service::SCHEDULE_IMMEDIATELY,
            'as' => NOTIFICATIONS_BOL_Service::SCHEDULE_AUTO
        ));
    }

    public function findList( $count )
    {
        $example = new OW_Example();
        $example->setLimitClause(0, $count);
        $example->setOrder('timeStamp DESC');

        return $this->findListByExample($example);
    }
}