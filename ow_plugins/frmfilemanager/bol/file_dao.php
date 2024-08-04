<?php
/**
 * frmfilemanager
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmfilemanager
 * @since 1.0
 */

class FRMFILEMANAGER_BOL_FileDao extends OW_BaseDao
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
     * @var FRMFILEMANAGER_BOL_FileDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMFILEMANAGER_BOL_FileDao
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
        return 'FRMFILEMANAGER_BOL_File';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmfilemanager_file';
    }

    /***
     * immediate sub files
     * @param $id
     * @param bool $includeDir
     * @return array<FRMFILEMANAGER_BOL_File>
     */
    public function getSubFiles($id, $includeDir=true){
        $ex = new OW_Example();
        $ex->andFieldLike('parent_id', $id);
        if (!$includeDir) {
            $ex->andFieldNotEqual('mime', 'directory');
        }
        $ex->setOrder('name ASC');
        return $this->findListByExample($ex);
    }

    /***
     * immediate sub folders
     * @param $id
     * @return array<FRMFILEMANAGER_BOL_File>
     */
    public function getSubDirs($id){
        $ex = new OW_Example();
        $ex->andFieldLike('parent_id', $id);
        $ex->andFieldEqual('mime', 'directory');
        $ex->setOrder('name ASC');
        return $this->findListByExample($ex);
    }

    /***
     * immediate sub files and folders
     * @param $id
     * @return array<FRMFILEMANAGER_BOL_File>
     */
    public function getSubIds($id){
        $ex = new OW_Example();
        $ex->andFieldLike('parent_id', $id);
        $ex->setOrder('name ASC');
        return $this->findIdListByExample($ex);
    }
}