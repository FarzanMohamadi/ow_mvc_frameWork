<?php
/**
 * Data Access Object for `base_theme_control_value` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ThemeControlValueDao extends OW_BaseDao
{
    const THEME_CONTROL_KEY = 'themeControlKey';
    const THEME_ID = 'themeId';
    const VALUE = 'value';

    /**
     * Singleton instance.
     *
     * @var BOL_ThemeControlValueDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ThemeControlValueDao
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
        return 'BOL_ThemeControlValue';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_theme_control_value';
    }

    public function deleteThemeControlValues( $themeId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::THEME_ID, (int) $themeId);

        $this->deleteByExample($example);
    }

    /**
     * @param string $key
     * @param int $themeId
     * @return BOL_ThemeControlValue
     */
    public function findByTcNameAndThemeId( $key, $themeId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::THEME_CONTROL_KEY, $key);
        $example->andFieldEqual(self::THEME_ID, (int) $themeId);

        return $this->findObjectByExample($example);
    }

    public function findByThemeId( $themeId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::THEME_ID, (int) $themeId);

        return $this->findListByExample($example);
    }

    public function deleteByTcNameAndThemeId( $key, $themeId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::THEME_CONTROL_KEY, $key);
        $example->andFieldEqual(self::THEME_ID, (int) $themeId);

        $this->deleteByExample($example);
    }
}
