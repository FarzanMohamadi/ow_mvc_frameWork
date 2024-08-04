<?php
/**
 * Data Access Object for `video_clip_featured` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.video.bol
 * @since 1.0
 */
class VIDEO_BOL_ClipFeaturedDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var VIDEO_BOL_ClipFeaturedDao
     */
    private static $classInstance;

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an instance of class.
     *
     * @return VIDEO_BOL_ClipFeaturedDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'VIDEO_BOL_ClipFeatured';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'video_clip_featured';
    }

    /**
     * Check if clip is featured
     * 
     * @param int $clipId
     * @return boolean
     */
    public function isFeatured( $clipId )
    {
        if ( !$clipId )
            return false;

        $example = new OW_Example();
        $example->andFieldEqual('clipId', $clipId);

        $clip = $this->findObjectByExample($example);

        return $clip !== null ? true : false;
    }

    /**
     * Marks clip as featured
     * 
     * @param int $clipId
     * @return boolean
     */
    public function markFeatured( $clipId )
    {
        if ( !$clipId )
            return false;

        if ( $this->isFeatured($clipId) )
            return true;

        $clip = new VIDEO_BOL_ClipFeatured();
        $clip->clipId = $clipId;

        $this->save($clip);

        return true;
    }

    /**
     * Marks clip as unfeatured
     * 
     * @param int $clipId
     * @return boolean
     */
    public function markUnfeatured( $clipId )
    {
        if ( !$clipId )
            return false;

        $example = new OW_Example();
        $example->andFieldEqual('clipId', $clipId);

        $this->deleteByExample($example);

        return true;
    }
}