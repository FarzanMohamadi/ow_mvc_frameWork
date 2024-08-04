<?php
/**
 * Data Access Object for `notifications_rule` table.
 *
 * @package ow_plugins.notifications.bol
 * @since 1.0
 */
class NOTIFICATIONS_BOL_ScheduleDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var NOTIFICATIONS_BOL_ScheduleDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NOTIFICATIONS_BOL_ScheduleDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $cache;
    /**
     * NOTIFICATIONS_BOL_ScheduleDao constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->cache = $this->findAll();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'NOTIFICATIONS_BOL_Schedule';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'notifications_schedule';
    }

    /**
     *
     * @param int $userId
     * @return NOTIFICATIONS_BOL_Schedule
     */
    public function findByUserId( $userId )
    {
        if(isset($this->cache)){
            foreach ($this->cache as $item){
                if ($item->userId == $userId){
                    return $item;
                }
            }

            return null;
        }
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findObjectByExample($example);
    }
}