<?php
/**
 * Data Access Object for `theme_content` table.  
 * 
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ThemeContentDao extends OW_BaseDao
{
    const THEME_ID = 'themeId';
    const TYPE = 'type';
    const VALUE = 'value';
    const VALUE_TYPE_ENUM_CSS = 'css';
    const VALUE_TYPE_ENUM_DECORATOR = 'decorator';
    const VALUE_TYPE_ENUM_MASTER_PAGE = 'master_page';
    const VALUE_TYPE_ENUM_MOBILE_MASTER_PAGE = 'm_master_page';

    /**
     * Singleton instance.
     *
     * @var BOL_ThemeContentDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ThemeContentDao
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
        return 'BOL_ThemeContent';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_theme_content';
    }

    /**
     * Returns theme content items for provided theme id.
     *
     * @param integer $themeId
     * @return array
     */
    public function findByThemeId( $themeId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::THEME_ID, $themeId);
        return $this->findListByExample($example, 24 * 3600, array(BOL_ThemeDao::CACHE_TAG_PAGE_LOAD_THEME, OW_CacheManager::TAG_OPTION_INSTANT_LOAD));
    }

    /**
     * Deletes theme content items for provided theme id.
     * 
     * @param string $themeId
     * @return integer
     */
    public function deleteByThemeId( $themeId )
    {
        $this->clearCache();
        $example = new OW_Example();
        $example->andFieldEqual(self::THEME_ID, $themeId);
        return $this->deleteByExample($example);
    }

    protected function clearCache()
    {
        OW::getCacheManager()->clean(array(BOL_ThemeDao::CACHE_TAG_PAGE_LOAD_THEME));
    }
}
