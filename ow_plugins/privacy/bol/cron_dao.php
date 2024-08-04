<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.privacy.bol
 * @since 1.0
 */
class PRIVACY_BOL_CronDao extends OW_BaseDao
{
    const USER_ID = 'userId';
    const ACTION = 'action';
    const VALUE = 'value';
    const IN_PROCESS = 'inProcess';
    const TIMESTAMP = 'timeStamp';

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
     * @var PRIVACY_BOL_CronDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PRIVACY_BOL_CronDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'PRIVACY_BOL_Cron';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'privacy_cron';
    }

    public function batchSaveOrUpdate( array $objects )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $objects);
    }

    public function getUpdatedActions($limit)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual(PRIVACY_BOL_CronDao::IN_PROCESS, 0);
        $ex->setOrder(PRIVACY_BOL_CronDao::TIMESTAMP);
        $ex->setLimitClause(0, $limit);

        $objectList = $this->findListByExample($ex);

        return $objectList;
    }

    public function setProcessStatus( $idList )
    {
        if ( empty($idList) )
        {
            return;
        }

        $query = " UPDATE IGNORE " . $this->getTableName() . " SET " . self::IN_PROCESS . "=1 WHERE id IN ( " . $this->dbo->mergeInClause($idList) . " ) ";
        $this->dbo->update($query);
    }
}