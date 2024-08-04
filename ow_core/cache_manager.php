<?php
/**
 * The class is a gateway for auth. adapters and provides common API to authenticate users.
 *
 * @package ow_core
 * @method static OW_CacheManager getInstance()
 * @since 1.0
 */
class OW_CacheManager
{
    use OW_Singleton;
    
    const CLEAN_ALL = 'all';
    const CLEAN_OLD = 'old';
    const CLEAN_MATCH_TAGS = 'match_tag';
    const CLEAN_MATCH_ANY_TAG = 'match_any_tag';
    const CLEAN_NOT_MATCH_TAGS = 'not_match_tags';
    const TAG_OPTION_INSTANT_LOAD = 'base.tag_option.instant_load';

    /**
     * @var OW_ICacheBackend
     */
    private $cacheBackend;

    /**
     * @var integer
     */
    private $lifetime;

    /**
     * @var boolean
     */
    private $cacheEnabled;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->cacheEnabled = !false;
    }

    public function getCacheEnabled()
    {
        return $this->cacheEnabled;
    }

    public function setCacheEnabled( $cacheEnabled )
    {
        $this->cacheEnabled = (bool) $cacheEnabled;
    }

    public function load( $key )
    {
        if ( $this->cacheAvailable() )
        {
            return $this->cacheBackend->load($key);
        }

        return null;
    }

    public function test( $key )
    {
        if ( $this->cacheAvailable() )
        {
            return $this->cacheBackend->test($key);
        }

        return false;
    }

    public function save( $data, $key, $tags = array(), $specificLifetime = false )
    {
        if ( $this->cacheAvailable() )
        {
            return $this->cacheBackend->save($data, $key, $tags, ($specificLifetime === false ? $this->lifetime : $specificLifetime));
        }

        return false;
    }

    public function remove( $key )
    {
        if ( $this->cacheAvailable() )
        {
            return $this->cacheBackend->remove($key);
        }

        return false;
    }

    public function clean( $tags = array(), $mode = self::CLEAN_MATCH_ANY_TAG )
    {
        if ( $this->cacheAvailable() )
        {
            return $this->cacheBackend->clean($tags, $mode);
        }

        return false;
    }

    /**
     * @param OW_ICacheBackend $cacheBackend
     */
    public function setCacheBackend( OW_ICacheBackend $cacheBackend )
    {
        $this->cacheBackend = $cacheBackend;
    }

    /**
     * @param int $lifetime
     */
    public function setLifetime( $lifetime )
    {
        $this->lifetime = $lifetime;
    }

    private function cacheAvailable()
    {
        return $this->cacheBackend !== null && $this->cacheEnabled;
    }
}