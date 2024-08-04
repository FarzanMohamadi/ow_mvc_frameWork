<?php
/**
 * Data Access Object for `notifications_unsubscribe` table.
 *
 * @package ow_plugins.notifications.bol
 * @since 1.0
 */
class NOTIFICATIONS_BOL_UnsubscribeDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var NOTIFICATIONS_BOL_UnsubscribeDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NOTIFICATIONS_BOL_UnsubscribeDao
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
        return 'NOTIFICATIONS_BOL_Unsubscribe';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'notifications_unsubscribe';
    }

    /**
     * 
     * @param $userId
     * @return NOTIFICATIONS_BOL_Schedule
     */
    public function findByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findObjectByExample($example);
    }

    /**
     * 
     * @param $userId
     * @return NOTIFICATIONS_BOL_Schedule
     */
    public function findByCode( $code )
    {
        $example = new OW_Example();
        $example->andFieldEqual('code', $code);

        return $this->findObjectByExample($example);
    }

    public function deleteExpired( $timeStamp )
    {
        $example = new OW_Example();
        $example->andFieldLessThan('timeStamp', $timeStamp);

        $this->deleteByExample($example);
    }
}