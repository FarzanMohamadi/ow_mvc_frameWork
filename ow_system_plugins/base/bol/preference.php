<?php
/**
 * Data Transfer Object for `preference` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_Preference extends OW_Entity
{
    /**
     * @var string
     */
    public $key;
    
    /**
     * @var string
     */
    public $sectionName;
    
    /**
     * @var string
     */
    public $defaultValue;
    
    /**
     * @var int
     */
    public $sortOrder;
}
