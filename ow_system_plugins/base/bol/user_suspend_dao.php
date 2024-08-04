<?php
/**
 * Singleton. 'Suspended User' Data Access Object
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_UserSuspendDao extends OW_BaseDao
{

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
     * @var BOL_UserSuspendDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_UserSuspendDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_user_suspend';
    }

    public function getDtoClassName()
    {
        return 'BOL_UserSuspend';
    }

    /**
     * Get suspend reason
     *
     * @param integer $userId
     * @return string
     */
    public function getSuspendReason( $userId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);

        $reason =  $this->findObjectByExample($ex);

        return $reason->message ? $reason->message : null;
    }

    public function findByUserId( $id )
    {
        $ex = new OW_Example();

        $ex->andFieldEqual('userId', $id);

        return $this->findObjectByExample($ex);
    }

    public function findSupsendStatusForUserList( $idList )
    {
        $query = "SELECT `userId` FROM `" . $this->getTableName() . "` WHERE `userId` IN (" . $this->dbo->mergeInClause($idList) . ")";

        return $this->dbo->queryForColumnList($query);
    }
}