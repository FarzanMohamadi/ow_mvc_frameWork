<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmvitrin.bol
 * @since 1.0
 */
class FRMVITRIN_BOL_ItemDao extends OW_BaseDao
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
        return 'FRMVITRIN_BOL_Item';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmvitrin_item';
    }

    /***
     * @param $itemId
     * @return FRMVITRIN_BOL_Item
     */
    public function getItem($itemId){
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $itemId);
        return $this->findObjectByExample($ex);
    }

    /***
     * @return array
     */
    public function getItems(){
        $ex = new OW_Example();
        $ex->setOrder('`order` ASC');
        return $this->findListByExample($ex);
    }

    /***
     * @param $title
     * @param $description
     * @param $order
     * @param $logo
     * @param $businessModel
     * @param $language
     * @param $url
     * @param $targetMarket
     * @param $vendor
     * @return FRMVITRIN_BOL_Item|null
     */
    public function saveItem($title, $description, $order, $logo, $businessModel, $language, $url, $targetMarket, $vendor){
        if(!OW::getUser()->isAuthenticated()){
            return null;
        }
        $item = new FRMVITRIN_BOL_Item();
        $item->title = $title;
        $item->description = $description;
        $item->order = $order;
        $item->logo = $logo;
        $item->userId = OW::getUser()->getId();
        $item->businessModel = $businessModel;
        $item->language = $language;
        $item->url = $url;
        $item->targetMarket = $targetMarket;
        $item->vendor = $vendor;
        $this->save($item);
        return $item;
    }

    public function getMaxOrder(){
        $query = "SELECT MAX(`order`) FROM `{$this->getTableName()}`";
        $maxOrder = $this->dbo->queryForColumn($query);
        if ($maxOrder == null) {
            $maxOrder = 0;
        }
        return $maxOrder;
    }

    /***
     * @param $itemId
     * @param $title
     * @param $description
     * @param $logo
     * @param $businessModel
     * @param $language
     * @param $url
     * @param $targetMarket
     * @param $vendor
     * @return mixed|null
     */
    public function update($itemId, $title, $description, $logo, $businessModel, $language, $url, $targetMarket, $vendor){
        if(!OW::getUser()->isAuthenticated()){
            return null;
        }
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $itemId);
        $item = $this->findObjectByExample($ex);
        if($item!=null) {
            $item->title = $title;
            $item->description = $description;
            $item->logo = $logo;
            $item->businessModel = $businessModel;
            $item->language = $language;
            $item->url = $url;
            $item->targetMarket = $targetMarket;
            $item->vendor = $vendor;
            $this->save($item);
        }
        return $item;
    }

}
