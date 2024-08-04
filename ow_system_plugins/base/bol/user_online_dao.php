<?php
/**
 * Data Access Object for `user_online` table.  
 * 
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_UserOnlineDao extends OW_BaseDao
{
    const USER_ID = 'userId';
    const ACTIVITY_STAMP = 'activityStamp';
    const CONTEXT = 'context';
    const CONTEXT_VAL_DESKTOP = 1;
    const CONTEXT_VAL_MOBILE = 2;
    const CONTEXT_VAL_API = 4;
    const CONTEXT_VAL_CLI = 8;

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
     * @var BOL_UserOnlineDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_UserOnlineDao
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
        return 'BOL_UserOnline';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_user_online';
    }

    /**
     * 
     * @param integer $userId
     * @return BOL_UserOnline
     */
    public function findByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);

        // Delete if expired
        $result = $this->findObjectByExample($example);
        if ($result !== null && $result->activityStamp < BOL_UserService::getInstance()->getOnlineUserExpirationTimestamp()) {
            $this->delete($result);
            return null;
        }
        return $result;
    }

    public function findOnlineUserIdListFromIdList( $idList )
    {
        if ( empty($idList) )
        {
            return array();
        }

        $query = "SELECT * FROM `" . $this->getTableName() . "` WHERE `" . self::USER_ID . "` IN (" . $this->dbo->mergeInClause($idList) . ")";

        // Delete expired
        $all = $this->dbo->queryForList($query);
        $expirationTimestamp = BOL_UserService::getInstance()->getOnlineUserExpirationTimestamp();
        $result = array();
        foreach ($all as $userOnline) {
            if ($userOnline['activityStamp'] < $expirationTimestamp) {
                $this->deleteById($userOnline['id']);
            } else {
                $result[] = $userOnline;
            }
        }

        return $result;
    }

    public function deleteExpired( $timestamp )
    {
        $query = "DELETE FROM `{$this->getTableName()}` WHERE `activityStamp` < ?";

        return $this->dbo->query($query, array($timestamp));
    }

    public function save($entity) {
        $event = new OW_Event('frmsecurityessentials.on.check.object.before.save.or.update', array('entity'=>$entity,'entityClass'=>get_class($entity)));
        OW::getEventManager()->trigger($event);

        $entity->id = (int) $entity->id;
        if ( $entity->id > 0 ) {
            $this->dbo->updateObject($this->getTableName(), $entity);
        } else {
            $sql = "INSERT INTO " . $this->getTableName() .
                   "(`userId`, `activityStamp`, `context`)
                    VALUES
                    (:userId, :activityStamp, :context)
                    ON DUPLICATE KEY UPDATE 
                    `activityStamp` = :activityStamp, `context` = :context;";
            /** @var BOL_UserOnline $entity */
            $params = array('userId' => $entity->userId, 'activityStamp' => $entity->activityStamp, 'context' => $entity->context);
            $this->dbo->query($sql, $params);
        }
    }
}
