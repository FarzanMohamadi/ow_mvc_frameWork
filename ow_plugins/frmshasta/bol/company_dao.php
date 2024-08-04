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
class FRMSHASTA_BOL_CompanyDao extends OW_BaseDao
{
    private static $classInstance;

    /***
     * @return FRMSHASTA_BOL_CompanyDao
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
        return 'FRMSHASTA_BOL_Company';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmshasta_company';
    }

    /***
     * @param $name
     * @param $parentId
     * @param $imageUrl
     * @param null $id
     * @return FRMSHASTA_BOL_Company
     */
    public function saveCompany($name, $parentId, $imageUrl, $id = null) {
        $company = null;
        if ($id != null) {
            $company = $this->findById($id);
        } else {
            $company = new FRMSHASTA_BOL_Company();
        }
        $company->name = $name;
        $company->parentId = !empty($parentId)?$parentId:0;
        $company->imageUrl = $imageUrl;
        $this->save($company);
        return $company;
    }

    /***
     * @param $parentId
     * @return FRMSHASTA_BOL_Company
     */
    public function findByParentId($parentId) {
        $example = new OW_Example();
        $example->andFieldEqual('parentId', $parentId);
        return $this->findListByExample($example);
    }

    /***
     * @return array
     */
    public function findRoots() {
        $example = new OW_Example();
        $example->andFieldEqual('parentId', 0);
        return $this->findListByExample($example);
    }
}
