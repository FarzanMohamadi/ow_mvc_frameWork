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
class FRMSHASTA_BOL_SpecialCategoryDao extends OW_BaseDao
{
    private static $classInstance;

    /***
     * @return FRMSHASTA_BOL_SpecialCategoryDao
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
        return 'FRMSHASTA_BOL_SpecialCategory';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmshasta_special_category';
    }

    /***
     * @param $categoryId
     * @return FRMSHASTA_BOL_SpecialCategory
     */
    public function saveSpecialCategory($categoryId) {
        $category = null;
        if ($categoryId != null) {
            $example = new OW_Example();
            $example->andFieldEqual('categoryId', $categoryId);
            $category = $this->findObjectByExample($example);
        }

        if ($category != null) {
            return $category;
        }

        $category = new FRMSHASTA_BOL_SpecialCategory();
        $category->categoryId = $categoryId;
        $this->save($category);
        return $category;
    }

    /***
     * @param $categoryId
     */
    public function deleteSpecialCategory($categoryId) {
        $example = new OW_Example();
        $example->andFieldEqual('categoryId', $categoryId);
        $this->deleteByExample($example);
    }
}
