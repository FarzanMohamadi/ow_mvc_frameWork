<?php
/**
 * Site statistics service.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.7.6
 */
class BOL_SiteStatisticService
{
    /**
     * Site statistics dao
     * @var BOL_SiteStatisticDao
     */
    private $siteStatisticsDao;

    /**
     * Singleton instance.
     *
     * @var BOL_SiteStatisticService
     */
    private static $classInstance;

    /**
     * Period type last year
     */
    const PERIOD_TYPE_LAST_YEAR = 'last_year';

    /**
     * Period type last 30 days
     */
    const PERIOD_TYPE_LAST_30_DAYS = 'last_30_days';

    /**
     * Period type last 7 days
     */
    const PERIOD_TYPE_LAST_7_DAYS = 'last_7_days';

    /**
     * Period type yesterday
     */
    const PERIOD_TYPE_YESTERDAY = 'yesterday';

    /**
     * Period type today
     */
    const PERIOD_TYPE_TODAY = 'today';

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->siteStatisticsDao = BOL_SiteStatisticDao::getInstance();
    }

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_SiteStatisticService
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
     * Add entity
     * 
     * @param string $entityType
     * @param integer $entityId
     * @param integer $entityCount
     * @return void
     */
    public function addEntity($entityType, $entityId, $entityCount = 1)
    {
        $siteStatisticsDto = new BOL_SiteStatistic();
        $siteStatisticsDto->entityId = $entityId;
        $siteStatisticsDto->entityType = $entityType;
        $siteStatisticsDto->entityCount = $entityCount;
        $siteStatisticsDto->timeStamp = time();

        $this->siteStatisticsDao->save($siteStatisticsDto);
    }

    /**
     * Get statistics
     *
     * @param array $entities
     * @param string $period
     * @return array
     */
    public function getStatistics(array $entities, $period = self::PERIOD_TYPE_TODAY)
    {
        switch ($period)
        {
            case self::PERIOD_TYPE_LAST_YEAR :
                $statistics =  $this->siteStatisticsDao->getLastYearStatistics($entities);
                break;

            case self::PERIOD_TYPE_LAST_30_DAYS :
                $statistics =  $this->siteStatisticsDao->getLast30DaysStatistics($entities);
                break;

            case self::PERIOD_TYPE_LAST_7_DAYS :
                $statistics =  $this->siteStatisticsDao->getLast7DaysStatistics($entities);
                break;

            case self::PERIOD_TYPE_YESTERDAY :
                $statistics =  $this->siteStatisticsDao->getYesterdayStatistics($entities);
                break;

            case self::PERIOD_TYPE_TODAY :
            default :
                $statistics =  $this->siteStatisticsDao->getTodayStatistics($entities);
        }

        return $statistics;
    }
}