<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmshasta.bol
 * @since 1.0
 */
class FRMSHASTA_BOL_UserCompanyDao extends OW_BaseDao
{
    private static $classInstance;

    /***
     * @return FRMSHASTA_BOL_UserCompanyDao
     */
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
        return 'FRMSHASTA_BOL_UserCompany';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmshasta_user_company';
    }

    /***
     * @param $userId
     * @param $companyId
     * @param null $id
     * @return FRMSHASTA_BOL_UserCompany
     */
    public function saveCompany($userId, $companyId, $id = null) {
        $company = null;
        if ($id != null) {
            $company = $this->findById($id);
        }
        if ($company == null) {
            $company = $this->findByUser($userId);
        }
        if ($company == null) {
            $company = new FRMSHASTA_BOL_UserCompany();
        }
        $company->userId = $userId;
        $company->companyId = $companyId;
        $this->save($company);
        return $company;
    }

    /***
     * @param $userId
     * @return FRMSHASTA_BOL_UserCompany
     */
    public function findByUser($userId) {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        return $this->findObjectByExample($example);
    }

    /***
     * @param $companyId
     * @return FRMSHASTA_BOL_UserCompany
     */
    public function findByCompany($companyId) {
        $example = new OW_Example();
        $example->andFieldEqual('companyId', $companyId);
        return $this->findListByExample($example);
    }
}
