<?php
/**
 * Data Access Object for `base_theme_control` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ThemeControlDao extends OW_BaseDao
{
    const ATTRIBUTE = 'attribute';
    const SELECTOR = 'selector';
    const DEFAULT_VALUE = 'defaultValue';
    const TYPE = 'type';
    const THEME_ID = 'themeId';
    const KEY = 'key';
    const SECTION = 'section';
    const LABEL = 'label';
    const DESC = 'description';
    const MOBILE = 'mobile';

    const TYPE_VALUE_COLOR = 'color';
    const TYPE_VALUE_TEXT = 'text';
    const TYPE_VALUE_FONT = 'font';
    const TYPE_VALUE_IMAGE = 'image';

    /**
     * Singleton instance.
     *
     * @var BOL_ThemeControlDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ThemeControlDao
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
        return 'BOL_ThemeControl';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_theme_control';
    }

    public function deleteThemeControls( $themeId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::THEME_ID, $themeId);
        $this->deleteByExample($example);
    }

    public function findThemeControls( $themeId )
    {
        $query = "SELECT `c`.`id` AS `cid`, `c`.*, `cv`.* FROM `" . $this->getTableName() . "` AS `c`
            LEFT JOIN ( SELECT * FROM `" . BOL_ThemeControlValueDao::getInstance()->getTableName() . "` WHERE `themeId` = :themeId2 )
                AS `cv` ON (`c`.`key` = `cv`.`themeControlKey`)
            WHERE `c`.`themeId` = :themeId ORDER BY `" . self::LABEL . "`";

        return $this->dbo->queryForList($query, array('themeId' => $themeId, 'themeId2' => $themeId));
    }
}
