<?php
/**
 * Base validator class.
 *
 * @package ow_core
 * @since 1.8.3
 */
interface OW_IFilter
{

    /**
     * Filters 
     *
     * @param mixed $value
     * @return boolean
     */
    public function filter( $value );

    /**
     * Returns JS code to validate form element data
     *
     * @return string
     */
    public function getJsFilter();
}

/**
 * @package ow_core
 * @since 1.8.3
 */
class TrimFilter implements OW_IFilter
{

    /**
     * @param string $value
     * @return string
     */
    public function filter( $value )
    {
        return trim($value);
    }

    /**
     * @return string
     */
    public function getJsFilter()
    {
        return "{filter : function( data ){return data.trim()}}";
    }
}

/**
 * @package ow_core
 * @since 1.8.3
 */
class StripTagsFilter implements OW_IFilter
{
    /**
     * @param string $value
     * @return string
     */
    public function filter( $value )
    {
        return strip_tags($value);
    }

    public function getJsFilter()
    {
        return "{filter : function( data ){return $(data).text()}}";
    }
}
