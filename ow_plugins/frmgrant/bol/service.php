<?php
class FRMGRANT_BOL_Service
{
    private static $classInstance;
    private $grantDao;
    private $configs = array();
    const CONF_GRANTS_COUNT_ON_PAGE = 'grants_count_on_page';

    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    protected function __construct()
    {
        $this->grantDao = FRMGRANT_BOL_GrantDao::getInstance();
        $this->configs[self::CONF_GRANTS_COUNT_ON_PAGE] =6;
    }
    public function getConfigs()
    {
        return $this->configs;
    }

    public function saveGrant( FRMGRANT_BOL_Grant $grant )
    {
        $this->grantDao->save($grant);
    }

    public function deleteGrant( $grantId )
    {

        $this->grantDao->deleteById($grantId);
    }
    public function findGrantById( $grantId )
    {
        return $this->grantDao->findById((int) $grantId);
    }
    public function findAllGrants()
    {
        return $this->grantDao->findAll();
    }
    public function findAllGrantsCount()
    {
        return $this->grantDao->findGrantsCount();
    }
    public function findGrantsOrderedList( $page, $tgrantsCount = null)
    {
        if ( $page === null )
        {
            $first = 0;
            $count = $this->configs[self::CONF_GRANTS_COUNT_ON_PAGE];
        }
        else
        {
            $page = ( $page === null ) ? 1 : (int) $page;
            $count = $this->configs[self::CONF_GRANTS_COUNT_ON_PAGE];
            $first = ( $page - 1 ) * $count;
        }

        return $this->grantDao->findOrderedList($first, $count);
    }
    public function findGrantsOrderedListCount()
    {
        return $this->grantDao->findGrantsCount();
    }
    public function getListingDataGrant( array $grants )
    {
        $resultArray = array();

        foreach ($grants as $grantItem) {
            $title = UTIL_String::truncate(strip_tags($grantItem->getTitle()), 100, "...");
            $resultArray[$grantItem->getId()] = array(
                'title' => $title,
                'url' => OW::getRouter()->urlForRoute('frmgrant.view', array('grantId' => $grantItem->getId()))
            );
        }
        return $resultArray;
    }
}
