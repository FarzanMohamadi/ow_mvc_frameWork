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
class FRMSHASTA_BOL_FileCategoryDao extends OW_BaseDao
{
    private static $classInstance;

    /***
     * @return FRMSHASTA_BOL_FileCategoryDao
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
        return 'FRMSHASTA_BOL_FileCategory';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmshasta_category';
    }

    /***
     * @param $name
     * @param $monthFilter
     * @param $yearFilter
     * @param null $id
     * @param $concept
     * @return FRMSHASTA_BOL_FileCategory|null
     */
    public function saveCategory($name, $monthFilter, $yearFilter, $concept = 1, $id = null) {
        $category = null;
        if ($id != null) {
            $category = $this->findById($id);
        } else {
            $category = new FRMSHASTA_BOL_FileCategory();
        }
        $category->name = $name;
        $category->monthFilter = $monthFilter;
        $category->yearFilter = $yearFilter;
        $category->concept = $concept;
        $this->save($category);
        return $category;
    }
}
