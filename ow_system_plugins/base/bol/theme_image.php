<?php
/**
 * Data Transfer Object for `base_theme_image` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ThemeImage extends OW_Entity
{
    /**
     * @var string
     */
    public $filename;

    /**
     * @var integer
     */
    public $addDatetime;

    /**
     * @var string
     */
    public $dimensions;

    /**
     * @var string
     */
    public $filesize;

    /**
     * @var string
     */
    public $title;

    public function getFilename()
    {
        return $this->filename;
    }

    /**
     *
     * @param string $filename
     * @return BOL_ThemeImage
     */
    public function setFilename( $filename )
    {
        $this->filename = $filename;
        return $this;
    }
}