<?php
/**
 * FRM Update Server
 */

/**
 * Data Access Object for `UpdateInformation` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmupdateserver.bol
 * @since 1.0
 */
class FRMUPDATESERVER_BOL_ItemDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var FRMUPDATESERVER_BOL_ItemDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMUPDATESERVER_BOL_Itemdao
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
        return 'FRMUPDATESERVER_BOL_Item';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmupdateserver_items';
    }

    /***
     * @param $key
     * @return mixed
     */
    public function getItemByKey($key)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('key', $key);
        $item = $this->findObjectByExample($ex);

        return $item;
    }

    /***
     * @param $id
     * @return mixed
     */
    public function getItemById($id)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $id);
        $item = $this->findObjectByExample($ex);

        return $item;
    }

    /***
     * @param null $type
     * @return array
     */
    public function getItems($type = null)
    {
        $ex = new OW_Example();
        if($type!=null) {
            $ex->andFieldEqual('type', $type);
        }
        $ex->setOrder('`order` ASC');
        return $this->findListByExample($ex);
    }

    public function getMaxOrder(){
        $query = "SELECT MAX(`order`) FROM `{$this->getTableName()}`";
        $maxOrder = $this->dbo->queryForColumn($query);
        if ($maxOrder == null) {
            $maxOrder = 0;
        }
        return $maxOrder;
    }

}