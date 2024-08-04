<?php
/**
 * Data Access Object for `frmcontactus_department` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcontactus.bol
 * @since 1.0
 */
class FRMCONTACTUS_BOL_UserInformationDao extends OW_BaseDao
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
     * @var FRMCONTACTUS_BOL_UserInformationDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMCONTACTUS_BOL_UserInformationDao
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
        return 'FRMCONTACTUS_BOL_UserInformation';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmcontactus_user_information';
    }

    public function findByLabel($department,$first,$count)
    {
        $data = array();

        $queryGetAllData = 'select * from '.  self::getTableName() .' where LOWER(label) = LOWER("'.$department.'") ORDER BY `timeStamp` DESC limit '. $first.','.$count ;
        $data['data'] = OW::getDbo()->queryForList($queryGetAllData);
        return $data;
    }

    public function deleteByLabel( $label )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('label', $label);

        return $this->deleteByExample($ex);
    }
    public function getCountByDep( $label )
    {
        $sql = 'SELECT COUNT(*) FROM ' . self::getTableName() . ' where LOWER(label) = LOWER("'.$label.'")' ;

        return $this->dbo->queryForColumn($sql);
    }
}