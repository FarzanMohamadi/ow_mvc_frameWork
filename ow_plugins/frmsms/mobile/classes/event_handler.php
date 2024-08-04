<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsms.bol
 * @since 1.0
 */
class FRMSMS_MCLASS_EventHandler
{
    /**
     * @var FRMSMS_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return FRMSMS_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct() { }

    public function init()
    {
        FRMSMS_CLASS_EventHandler::getInstance()->genericInit();
    }

}