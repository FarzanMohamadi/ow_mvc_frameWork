<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.photo.bol
 * @since 1.6.1
 */
class PHOTO_BOL_PhotoCacheDao extends OW_BaseDao
{
    CONST KEY = 'key';
    CONST CREATE_TIMESTAMP = 'createTimestamp';
    
    CONST CACHE_LIFETIME = 10;
    
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
        return OW_DB_PREFIX . 'photo_cache';
    }
    
    public function getDtoClassName()
    {
        return 'PHOTO_BOL_PhotoCache';
    }
    
    public function getKey( $searchVal )
    {
        return crc32(OW::getUser()->getId() . $searchVal);
    }
    
    public function getKeyAll( $searchVal )
    {
        return crc32(OW::getUser()->getId() . $searchVal . 'all');
    }

    public function findCacheByKey( $key )
    {
        if ( empty($key) )
        {
            return NULL;
        }
        
        $sql = 'SELECT *
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::KEY . '` = :key
            LIMIT 1';

        $result = $this->dbo->queryForObject($sql, $this->getDtoClassName(), array('key' => $key));

        if ($result !== null && $result->createTimestamp <= time() - self::CACHE_LIFETIME * 60) {
            $this->delete($result);
            return null;
        }

        return $result;
    }
    
    public function cleareCache()
    {
        return $this->dbo->query('DELETE FROM `' . $this->getTableName() . '`
            WHERE `' . self::CREATE_TIMESTAMP . '` <= :time', array('time' => time() - self::CACHE_LIFETIME * 60));
    }
    public function invalidateCacheItem($key){
        if ( empty($key) )
        {
            return null;
        }
        $sql = 'DELETE
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::KEY . '` = :key';

        return $this->dbo->query($sql,array('key'=> $key));
    }
}
