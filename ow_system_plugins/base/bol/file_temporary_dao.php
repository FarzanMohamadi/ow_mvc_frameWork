<?php
/**
 * Data Access Object for `base_file_temporary` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.7.5
 */
class BOL_FileTemporaryDao extends OW_BaseDao
{

    /**
     * Singleton instance.
     *
     * @var BOL_FileTemporaryDao
     */
    private static $classInstance;

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an instance of class.
     *
     * @return BOL_FileTemporaryDao
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
        return 'BOL_FileTemporary';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'file_temporary';
    }

    /**
     * Find files by user Id
     *
     * @param int $userId
     *
     * @param string $orderBy
     * @return array
     */
    public function findByUserId( $userId, $orderBy = 'timestamp' )
    {
        if ( !$userId )
        {
            return null;
        }
        
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        
        if ( $orderBy == 'timestamp' )
        {
            $example->setOrder('`addDatetime` ASC');
        }
        else 
        {
            $example->setOrder('`order` ASC');
        }

        return $this->findListByExample($example);
    }
    
    public function findLimitedFiles( $limit = BOL_FileTemporaryService::TEMPORARY_FILE_LIVE_LIMIT )
    {
        $sql = 'SELECT `id`
            FROM `' . $this->getTableName() . '`
            WHERE `addDatetime` <= :limit';
        
        return $this->dbo->queryForColumnList($sql, array('limit' => $limit));
    }
}