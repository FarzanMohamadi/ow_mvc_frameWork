<?php
/**
 * Data Access Object for `notifications_rule` table.
 *
 * @package ow_plugins.notifications.bol
 * @since 1.0
 */
class NOTIFICATIONS_BOL_RuleDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var NOTIFICATIONS_BOL_RuleDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NOTIFICATIONS_BOL_RuleDao
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
        return 'NOTIFICATIONS_BOL_Rule';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'notifications_rule';
    }

    /**
     * 
     * @param unknown_type $key
     * @param unknown_type $userId
     * @return unknown_type
     */
    public function findRule( $key, $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('action', $userId);
        $example->andFieldEqual('userId', $userId);

        return $this->findObjectByExample($example);
    }

    public function findRuleList( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        return $this->findListByExample($example);
    }

    public function findRuleListByUserIds($userIds){
        if (empty($userIds)) {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray('userId', $userIds);
        $res = $this->findListByExample($example);

        $data = array();
        foreach ($res as $item) {
            if (!isset($data[$item->userId])) {
                $data[$item->userId] = array();
            }
            $data[$item->userId][] = $item;
        }
        foreach ($userIds as $userId) {
            if (!isset($data[$userId])) {
                $data[$userId] = array();
            }
        }
        return $data;
    }
}