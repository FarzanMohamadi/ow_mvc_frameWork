<?php
/**
 * Data Access Object for `forum_update_search_index` table
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
class FORUM_BOL_UpdateSearchIndexDao extends OW_BaseDao
{
    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Class instance
     *
     * @var FORUM_BOL_UpdateSearchIndexDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return FORUM_BOL_UpdateSearchIndexDao
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
        return 'FORUM_BOL_UpdateSearchIndex';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'forum_update_search_index';
    }

    /**
     * Find first queue
     * 
     * @return FORUM_BOL_UpdateSearchIndex
     */
    public function findFirstQueue()
    {
        $example = new OW_Example();
        $example->setOrder('`priority` DESC, `id` ASC');

        return $this->findObjectByExample($example);
    }

    /**
     * Add a queue
     * 
     * @param integer $entityId
     * @param string $type
     * @param integer $priority
     * @return void
     */
    public function addQueue($entityId, $type, $priority = FORUM_BOL_UpdateSearchIndexService::NORMAL_PRIORITY)
    {
        $updateSearchIndexDto = new FORUM_BOL_UpdateSearchIndex();
        $updateSearchIndexDto->entityId = $entityId;
        $updateSearchIndexDto->type     = $type;
        $updateSearchIndexDto->priority = $priority;

        $this->save($updateSearchIndexDto);
    }
}