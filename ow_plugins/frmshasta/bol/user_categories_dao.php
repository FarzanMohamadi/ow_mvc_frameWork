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
class FRMSHASTA_BOL_UserCategoriesDao extends OW_BaseDao
{
    private static $classInstance;

    /***
     * @return FRMSHASTA_BOL_UserCategoriesDao
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
        return 'FRMSHASTA_BOL_UserCategories';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmshasta_user_categories';
    }

    public function findByUser($userId = null) {
        if ($userId == null) {
            return null;
        }
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        return $this->findObjectByExample($example);
    }

    public function saveCategories($userId, $categories) {
        $categoriesObject = $this->findByUser($userId);
        if ($categoriesObject == null) {
            $categoriesObject = new FRMSHASTA_BOL_UserCategories();
        }
        $categoriesObject->categories = $categories;
        $categoriesObject->userId = $userId;
        $this->save($categoriesObject);
        return $categoriesObject;
    }
}
