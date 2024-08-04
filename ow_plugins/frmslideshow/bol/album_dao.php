<?php
/**
 * frmslideshow
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmslideshow
 * @since 1.0
 */
class FRMSLIDESHOW_BOL_AlbumDao extends OW_BaseDao
{

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var FRMSLIDESHOW_BOL_AlbumDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMSLIDESHOW_BOL_AlbumDao
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
        return 'FRMSLIDESHOW_BOL_Album';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmslideshow_album';
    }

    /***
     * @return array
     */
    public function getAlbums(){
        $ex = new OW_Example();
        $ex->setOrder('`id` ASC');
        return $this->findListByExample($ex);
    }

    /**
     * Finds Photo album by album name
     *
     * @param string $name
     * @return FRMSLIDESHOW_BOL_Album
     */
    public function findAlbumByName( $name )
    {
        $example = new OW_Example();
        $example->andFieldEqual('name', $name);
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }
}
