<?php
/**
 * Data Transfer Object for `base_theme_control` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ThemeControlValue extends OW_Entity
{
    /**
     * @var integer
     */
    public $themeControlKey;
    /**
     * @var integer
     */
    public $themeId;
    /**
     * @var mixed
     */
    public $value;

    /**
     * @return string $themeControlKey
     */
    public function getThemeControlKey()
    {
        return $this->themeControlKey;
    }

    /**
     * @return string $value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $themeControlKey
     */
    public function setThemeControlKey( $themeControlKey )
    {
        $this->themeControlKey = $themeControlKey;
    }

    /**
     * @param string $value
     */
    public function setValue( $value )
    {
        $this->value = $value;
    }

    public function getThemeId()
    {
        return $this->themeId;
    }

    public function setThemeId( $themeId )
    {
        $this->themeId = (int) $themeId;
    }
}