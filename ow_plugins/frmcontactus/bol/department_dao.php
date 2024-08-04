<?php
/**
 * Data Access Object for `frmcontactus_department` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcontactus.bol
 * @since 1.0
 */
class FRMCONTACTUS_BOL_DepartmentDao extends OW_BaseDao
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
     * @var FRMCONTACTUS_BOL_DepartmentDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMCONTACTUS_BOL_DepartmentDao
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
        return 'FRMCONTACTUS_BOL_Department';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmcontactus_department';
    }

    public function findIsExistLabel($label)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('label', $label);
        return $this->findObjectByExample($ex);
    }
}