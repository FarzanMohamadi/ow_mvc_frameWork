<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsms.bol
 * @since 1.0
 */
class FRMSMS_BOL_WaitlistDao extends OW_BaseDao
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

    public function getDtoClassName()
    {
        return 'FRMSMS_BOL_Waitlist';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmsms_waitlist';
    }

    public function findListByCount($max){
        $example = new OW_Example();
        $example->setOrder('`id` ASC');
        $example->setLimitClause(0, $max);
        return $this->findListByExample($example);
    }
}
