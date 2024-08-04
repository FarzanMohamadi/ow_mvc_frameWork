<?php
/**
 * Data Access Object for `photo_featured` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.bol
 * @since 1.0
 */
class PHOTO_BOL_PhotoFeaturedDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var PHOTO_BOL_PhotoFeaturedDao
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
     * @return PHOTO_BOL_PhotoFeaturedDao
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
        return 'PHOTO_BOL_PhotoFeatured';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'photo_featured';
    }

    /**
     * Check if photo is featured
     * 
     * @param int $photoId
     * @return boolean
     */
    public function isFeatured( $photoId )
    {
        if ( !$photoId )
            return false;

        $example = new OW_Example();
        $example->andFieldEqual('photoId', $photoId);

        $photo = $this->findObjectByExample($example);

        return $photo !== null ? true : false;
    }

    /**
     * Marks photo as featured
     * 
     * @param int $photoId
     * @return boolean
     */
    public function markFeatured( $photoId )
    {
        if ( !$photoId )
            return false;

        if ( $this->isFeatured($photoId) )
            return true;

        $photo = new PHOTO_BOL_PhotoFeatured();
        $photo->photoId = $photoId;

        $this->save($photo);

        return true;
    }

    /**
     * Marks photo as unfeatured
     * 
     * @param int $photoId
     * @return boolean
     */
    public function markUnfeatured( $photoId )
    {
        if ( !$photoId )
            return false;

        $example = new OW_Example();
        $example->andFieldEqual('photoId', $photoId);

        $this->deleteByExample($example);

        return true;
    }
}