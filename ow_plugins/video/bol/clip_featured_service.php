<?php
/**
 * Featured Clip Service Class.  
 * 
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.video.bol
 * @since 1.0
 * 
 */
final class VIDEO_BOL_ClipFeaturedService
{
    /**
     * @var VIDEO_BOL_ClipfeaturedDao
     */
    private $clipFeaturedDao;
    /**
     * Class instance
     *
     * @var VIDEO_BOL_ClipFeaturedService
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->clipFeaturedDao = VIDEO_BOL_ClipFeaturedDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return VIDEO_BOL_ClipFeaturedService
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Check if clip is featured
     * 
     * @param int $clipId
     * @return boolean
     */
    public function isFeatured( $clipId )
    {
        return $this->clipFeaturedDao->isFeatured($clipId);
    }

    /**
     * Marks clip as featured
     * 
     * @param int $clipId
     * @return boolean
     */
    public function markFeatured( $clipId )
    {
        $marked = $this->clipFeaturedDao->markFeatured($clipId);
        
        if ( $marked ) 
        {
            VIDEO_BOL_ClipService::getInstance()->cleanListCache();
        }

        $event = new OW_Event(VIDEO_BOL_ClipService::EVENT_AFTER_EDIT, array('clipId' => $clipId));
        OW::getEventManager()->trigger($event);
        
        return $marked;
    }

    /**
     * Marks clip as unfeatured
     * 
     * @param int $clipId
     * @return boolean
     */
    public function markUnfeatured( $clipId )
    {
        $marked = $this->clipFeaturedDao->markUnfeatured($clipId);
        
        if ( $marked )
        {
            VIDEO_BOL_ClipService::getInstance()->cleanListCache();
        }

        $event = new OW_Event(VIDEO_BOL_ClipService::EVENT_AFTER_EDIT, array('clipId' => $clipId));
        OW::getEventManager()->trigger($event);
        
        return $marked;
    }
}