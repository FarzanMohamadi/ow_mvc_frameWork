<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmupdateserver.bol
 * Date: 8/1/2018
 * Time: 9:21 AM
 */

class FRMUPDATESERVER_BOL_PluginInformationDao extends OW_BaseDao
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
     * @var FRMUPDATESERVER_BOL_PluginInformationDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMUPDATESERVER_BOL_PluginInformationDao
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
        return 'FRMUPDATESERVER_BOL_PluginInformation';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmupdateserver_plugin_information';
    }

    public function getItemInformationByCategoryId($CategoryId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('categories', $CategoryId);
        return $this->findListByExample($example);
    }
    public function addCategoryToItem($itemId,$categories)
    {
        $pluginInfo = new FRMUPDATESERVER_BOL_PluginInformation();
        $this->deleteByItemId($itemId);
        if($categories!=null) {
            $pluginInfo->setCategories(json_encode($categories));
            $pluginInfo->setItemId($itemId);
            $this->save($pluginInfo);
        }
    }

    public function deleteByCategoryId( $categoryId )
    {
        $ex = new OW_Example();
        $ex->andFieldLike('categories', $categoryId);
        return $this->deleteByExample($ex);
    }

    public function deleteByItemId( $itemId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('itemId', $itemId);
        return $this->deleteByExample($ex);
    }

    public function getItemInformationById($itemId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('itemId', $itemId);
        return $this->findObjectByExample($ex);

    }
}
