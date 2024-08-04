<?php
/**
 * Data Access Object for `base_theme_image` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ThemeImageDao extends OW_BaseDao
{
    const FILENAME = 'filename';

    /**
     * Singleton instance.
     *
     * @var BOL_ThemeImageDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ThemeImageDao
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
        return 'BOL_ThemeImage';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_theme_image';
    }

    /**
     * @return array<BOL_ThemeImage>
     */
    public function findGraphics()
    {
        $example = new OW_Example();
        $example->setOrder('`id` DESC');

        return $this->findListByExample($example);
    }

    /**
     * @param OW_Example$example
     * @param array $params
     */
    private function applyDatesBetweenFilter(OW_Example $example, $params)
    {
        if ( isset($params['start'], $params['end']) )
        {
            $start = $params['start'];
            $end = $params['end'];
            if ( !is_null($start) && !is_null($end) )
            {
                $example->andFieldBetween('addDatetime', $start, $end);
            }
        }
    }

    /**
     * @param OW_Example $example
     * @param array $params
     */
    private function applyLimitClause(OW_Example $example, $params)
    {
        if ( isset($params['page'], $params['limit']) && !is_null($params['page']) && !is_null($params['limit']) )
        {
            $page = $params['page'];
            $limit = $params['limit'];
            $first = ( $page - 1 ) * $limit;
            $example->setLimitClause($first, $limit);
        }
    }

    /**
     * @param array $params
     * @return array <BOL_ThemeImage>
     */
    public function filterGraphics($params)
    {
        $example = new OW_Example();
        $this->applyDatesBetweenFilter($example, $params);
        $this->applyLimitClause($example, $params);

        $example->setOrder('`id` DESC');
        return $this->findListByExample($example);
    }

    /**
     * @param int $id
     * @param array $params
     * @return array <BOL_ThemeImage>
     */
    public function getPrevImageList($id, $params)
    {
        $example = new OW_Example();
        $this->applyDatesBetweenFilter($example, $params);
        $this->applyLimitClause($example, $params);
        $example->andFieldGreaterThan('id', $id);
        $example->setOrder('`id` DESC');
        return $this->findListByExample($example);
    }

    /**
     * @param int $id
     * @param array $params
     * @return array <BOL_ThemeImage>
     */
    public function getNextImageList($id, $params)
    {
        $example = new OW_Example();
        $this->applyDatesBetweenFilter($example, $params);
        $this->applyLimitClause($example, $params);
        $example->andFieldLessThan('id', $id);
        $example->setOrder('`id` DESC');
        return $this->findListByExample($example);
    }
}
