<?php
/**
 * Data Access Object for `base_site_statistic` table.
 * 
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.7.6
 */
class BOL_SiteStatisticDao extends OW_BaseDao
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'entityType';

    /**
     * Entity id
     */
    const ENTITY_ID = 'entityId';

    /**
     * Entity count
     */
    const ENTITY_COUNT = 'entityCount';

    /**
     * Timestamp
     */
    const TIMESTAMP = 'timeStamp';

    /**
     * Report hour
     */
    const REPORT_TYPE_HOUR = 'hour';

    /**
     * Report day
     */
    const REPORT_TYPE_DAY = 'day';

    /**
     * Report month
     */
    const REPORT_TYPE_MONTH = 'month';

    /**
     * Singleton instance.
     *
     * @var BOL_SiteStatisticDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_SiteStatisticDao
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
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_SiteStatistic';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_site_statistic';
    }

    /**
     * Get last year statistics
     *
     * @param array $entityTypes
     * @return array
     */
    public function getLastYearStatistics(array $entityTypes)
    {
        $timeStart = strtotime('today -11 months');
        $timeEnd   = strtotime('today 23:59:59');

        return $this->getStatistics($this->
                getMonths(12), $entityTypes, $timeStart, $timeEnd, self::REPORT_TYPE_MONTH);
    }

    /**
     * Get last 30 days statistics
     *
     * @param array $entityTypes
     * @return array
     */
    public function getLast30DaysStatistics(array $entityTypes)
    {
        $timeStart = strtotime('today -29 days');
        $timeEnd   = strtotime('today 23:59:59');

        return $this->getStatistics($this->
                getDays(30), $entityTypes, $timeStart, $timeEnd, self::REPORT_TYPE_DAY);
    }

    /**
     * Get last 7 days statistics
     *
     * @param array $entityTypes
     * @return array
     */
    public function getLast7DaysStatistics(array $entityTypes)
    {
        $timeStart = strtotime('today -6 days');
        $timeEnd   = strtotime('today 23:59:59');

        return $this->getStatistics($this->
                getDays(7), $entityTypes, $timeStart, $timeEnd, self::REPORT_TYPE_DAY);
    }

    /**
     * Get yesterday statistics
     *
     * @param array $entityTypes
     * @return array
     */
    public function getYesterdayStatistics(array $entityTypes)
    {
        $timeStart = strtotime('yesterday');
        $timeEnd   = strtotime('yesterday 23:59:59');

        return $this->getStatistics($this->
                getHours(), $entityTypes, $timeStart, $timeEnd, self::REPORT_TYPE_HOUR);
    }

    /**
     * Get today statistics
     *
     * @param array $entityTypes
     * @return array
     */
    public function getTodayStatistics(array $entityTypes)
    {
        $timeStart  = strtotime('today');
        $timeEnd    = strtotime('today 23:59:59');

        return $this->getStatistics($this->
                getHours(), $entityTypes, $timeStart, $timeEnd, self::REPORT_TYPE_HOUR);
    }

    /**
     * Get months
     *
     * @param integer $count
     * @return array
     */
    protected function getMonths($count)
    {
        $categories = array();

        for ($i = $count-1; $i >= 0; $i--)
        {
            $categories[date('n' , strtotime('today -' . $i . ' months'))] = 0;
        }

        $categories[date('n' , strtotime('today'))] = 0;

        return $categories;
    }

    /**
     * Get days
     *
     * @param integer $count
     * @return array
     */
    protected function getDays($count)
    {
        $categories = array();

        for ($i = $count - 1; $i > 0; $i--)
        {
            $categories[date('j' , strtotime('today -' . $i . ' days'))] = 0;
        }

        $categories[date('j' , strtotime('today'))] = 0;

        return $categories;
    }

    /**
     * Get hours
     *
     * @return array
     */
    protected function getHours()
    {
        return array_pad(array(), 24, 0);
    }

    /**
     * Get statistics
     *
     * @param array $categories
     * @param array $entityTypes
     * @param integer $timeStart
     * @param integer $timeEnd
     * @param string $reportType
     * @return array
     */
    protected function getStatistics(array $categories, array $entityTypes, $timeStart, $timeEnd, $reportType)
    {
        // fill the array with empty values
        $report = array();
        foreach ($entityTypes as $type)
        {
            $report[$type] = $categories;
        }

        $query = '
            SELECT
                `' . self::ENTITY_TYPE . '`,
                DATE_FORMAT(FROM_UNIXTIME(`' . self::TIMESTAMP . '`), "' . $this->getReportDateFormat($reportType) . '") AS `category`,
                SUM(`' . self::ENTITY_COUNT . '`) as `count`
            FROM
                `' . $this->getTableName() . '`
            WHERE
                `' . self::ENTITY_TYPE . '` IN (' . $this->dbo->mergeInClause($entityTypes) . ')
                    AND
                `' . self::TIMESTAMP . '` >= ' . (int) $timeStart . '
                    AND
                `' . self::TIMESTAMP . '` <= ' . (int) $timeEnd   . '
            GROUP BY
                `' . self::ENTITY_TYPE . '`,
                `category`';

        $values =  $this->dbo->queryForList($query);

        if ( $values )
        {
            // fill report array with values
            foreach ($values as $value)
            {
                $report[$value['entityType']][$value['category']] = $value['count'];
            }
        }

        return $report;
    }

    /**
     * Get report date format
     *
     * @param string $reportType
     * @return string
     */
    protected function getReportDateFormat($reportType)
    {
        switch ( $reportType )
        {
            case self::REPORT_TYPE_MONTH :
                return '%c';
                break;

            case self::REPORT_TYPE_DAY :
                return '%e';
                break;

            case self::REPORT_TYPE_HOUR :
            default :
                return '%k';
        }
    }
}