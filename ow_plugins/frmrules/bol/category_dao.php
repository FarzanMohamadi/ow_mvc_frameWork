<?php
/**
 * FRM Rules
 */

/**
 * Data Access Object for `FRMTERMS_BOL_ItemVersion` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmrules.bol
 * @since 1.0
 */
class FRMRULES_BOL_CategoryDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var FRMRULES_BOL_CategoryDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMRULES_BOL_CategoryDao
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
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'FRMRULES_BOL_Category';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmrules_category';
    }

    /***
     * @param $name
     * @param $icon
     * @param $sectionId
     * @return FRMRULES_BOL_Category
     */
    public function saveCategory($name, $icon, $sectionId){
        $category = new FRMRULES_BOL_Category();
        $category->name = $name;
        $category->icon = $icon;
        $category->sectionId = $sectionId;
        $this->save($category);
        return $category;
    }

    /***
     * @param $categoryId
     * @return FRMRULES_BOL_Category
     */
    public function getCategory($categoryId){
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $categoryId);
        return $this->findObjectByExample($ex);
    }

    /***
     * @param $sectionId
     * @return array
     */
    public function getAllCategories($sectionId){
        $ex = new OW_Example();
        $ex->andFieldEqual('sectionId', $sectionId);
        return $this->findListByExample($ex);
    }

    /***
     * @param $id
     */
    public function deleteCategory($id)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $id);
        $this->deleteByExample($ex);
    }

    /***
     * @param $categoryId
     * @param $name
     * @param $icon
     * @return mixed
     */
    public function update($categoryId, $name, $icon){
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $categoryId);
        $category = $this->findObjectByExample($ex);
        $category->name = $name;
        $category->icon = $icon;
        $this->save($category);
        return $category;
    }
}