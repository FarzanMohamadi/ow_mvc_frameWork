<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.photo.bol
 * @since 1.6.1
 */
class PHOTO_BOL_SearchEntityDao extends OW_BaseDao
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
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'photo_search_entity';
    }
    
    public function getDtoClassName()
    {
        return 'PHOTO_BOL_SearchEntity';
    }
}
