<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class FRMECONETDANESH_BOL_Service
{


    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    public function __construct()
    {

    }


}
