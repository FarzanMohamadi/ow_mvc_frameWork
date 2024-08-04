<?php
/**
 * Search service.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_SearchService
{
    const USER_LIST_SIZE = 500;

    const SEARCH_RESULT_ID_VARIABLE = "OW_SEARCH_RESULT_ID";

    const EVENT_DELETE_EXPIRED_INCOMPLETE = 'base.delete_expired_incomplete';

    /**
     * @var BOL_SearchDao
     */
    private $searchDao;
    /**
     * @var BOL_SearchResultDao
     */
    private $searchResultDao;
    /**
     * Singleton instance.
     *
     * @var BOL_SearchService
     */
    private static $classInstance;

    /**
     * Constructor.
     *
     */
    private function __construct()
    {
        $this->searchDao = BOL_SearchDao::getInstance();
        $this->searchResultDao = BOL_SearchResultDao::getInstance();
    }

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_SearchService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * Save search Result. Returns search id.
     *
     * @param array $idList
     * @return int
     */
    public function saveSearchResult( array $idList )
    {
        $search = new BOL_Search();
        $search->timeStamp = time();

        $this->searchDao->save($search);

        $this->searchResultDao->saveSearchResult($search->id, $idList);

        $event = new OW_Event('base.after_save_search_result', array('searchDto' => $search, 'userIdList' => $idList), array());
        OW::getEventManager()->trigger($event);

        return $search->id;
    }

    /**
     * @param $listId
     * @param $first
     * @param $count
     * @param array $excludeList
     * @return array
     */
    public function getUserIdList( $listId, $first, $count, $excludeList = array() )
    {
        return $this->searchResultDao->getUserIdList($listId, $first, $count, $excludeList);
    }

    public function countSearchResultItem( $listId )
    {
        return $this->searchResultDao->countSearchResultItem($listId);
    }

    public function deleteExpireSearchResult()
    {
        $limit = 500;
        $list = $this->searchDao->findExpireSearchId($limit);

        if ( !empty($list) )
        {
            $this->searchResultDao->deleteSearchResultItems($list);
            $this->searchDao->deleteByIdList($list);
        }

        if (count($list) == $limit) {
            OW::getEventManager()->trigger(new OW_Event(self::EVENT_DELETE_EXPIRED_INCOMPLETE));
        }
    }
}