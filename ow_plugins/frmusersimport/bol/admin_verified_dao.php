<?php
/**
 * Class FRMUSERSIMPORT_BOL_AdminVerifiedDao
 */
class FRMUSERSIMPORT_BOL_AdminVerifiedDao extends OW_BaseDao
{
    
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'FRMUSERSIMPORT_BOL_AdminVerified';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmusersimport_admin_verified';
    }

    /**
     * @param array $list
     */
    public function saveList( array $list )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $list);
    }


    /**
     * @param $mobile
     * @return FRMUSERSIMPORT_BOL_AdminVerified
     */
    public function getAdminVerified($mobile)
    {
        $example = new OW_Example();
        $example->andFieldEqual('mobile',$mobile);
        return $this->findObjectByExample($example);
    }

    /***
     * @return array|string
     */
    public function getAllMobileNumbers(){
        $example = new OW_Example();
        $listObject = $this->findListByExample($example);
        return array_column((array)$listObject,'mobile');
    }

}
