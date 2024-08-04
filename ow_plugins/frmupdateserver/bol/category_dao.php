<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmupdateserver.bol
 * Date: 8/1/2018
 * Time: 9:21 AM
 */

class FRMUPDATESERVER_BOL_CategoryDao extends OW_BaseDao
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
     * @var FRMUPDATESERVER_BOL_CategoryDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMUPDATESERVER_BOL_CategoryDao
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
        return 'FRMUPDATESERVER_BOL_Category';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmupdateserver_category';
    }

    public function findIsExistLabel($label)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('label', $label);
        return $this->findObjectByExample($ex);
    }

    public function deleteByCategoryId( $categoryId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $categoryId);
        return $this->deleteByExample($ex);
    }

    public function getCategoryIdByLabel( $label )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('label', $label);
        return $this->findObjectByExample($ex);
    }

    public function getLabelByCategoryId( $categoryId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $categoryId);
        return $this->findObjectByExample($ex);
    }

}
