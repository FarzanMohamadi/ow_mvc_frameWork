<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

class FRMECONETDANESH_CLASS_EventHandler
{
    private static $classInstance;
    
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private function __construct()
    {
    }

    public function init()
    {


    }

}