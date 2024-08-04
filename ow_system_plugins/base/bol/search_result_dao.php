<?php
/**
 * Data Access Object for `base_search_result` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_SearchResultDao extends OW_BaseDao
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
     * @var BOL_SearchDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_Search
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
        return 'BOL_SearchResult';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_search_result';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function saveSearchResult( $searchId, array $idList )
    {
        $query = " INSERT INTO " . $this->getTableName() . " ( searchId, userId, sortOrder ) VALUES ";
        $count = 0;
        $values = '';

        $valuesList = array();

        foreach ( $idList as $order => $userId )
        {
            if ( $count > 0 )
            {
                $values .= ",";
            }

            $values .= " ( ?, ?, ? )";

            $valuesList[] = $searchId;
            $valuesList[] = $userId;
            $valuesList[] = $order;

            $count++;

            if ( $count >= 100 )
            {
                $this->dbo->query($query . $values, $valuesList);
                $count = 0;
                $values = '';
                $valuesList = array();
            }
        }

        if ( $count > 0 )
        {
            $this->dbo->query($query . $values, $valuesList);
        }
    }

    /**
     * Return search result item count
     *
     * @param int $listId
     * @param int $first
     * @param int $count
     * @return array
     */
    public function getUserIdList( $listId, $first, $count, $excludeList = array() )
    {
        $example = new OW_Example();
        $example->andFieldEqual('searchId', (int) $listId);
        $example->setOrder(' sortOrder ');
        $example->setLimitClause($first, $count);
        
        if ( !empty($excludeList) )
        {
            $example->andFieldNotInArray('userId', $excludeList);
        }

        $results = $this->findListByExample($example);

        $userIdList = array();

        foreach ( $results as $result )
        {
            $userIdList[] = $result->userId;
        }

        return $userIdList;
    }


    /**
     * Return search result item count
     *
     * @param int $listId
     * @return int
     */
    public function countSearchResultItem( $listId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('searchId', (int) $listId);

        return $this->countByExample($example);
    }

    /**
     * Return search result item count
     *
     * @param array $listId
     */
    public function deleteSearchResultItems( array $listId )
    {
        if ( empty($listId) )
        {
            return;
        }

        $example = new OW_Example();
        $example->andFieldInArray('searchId', $listId);

        $this->deleteByExample($example);
    }
}