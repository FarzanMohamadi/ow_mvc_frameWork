<?php
/**
 * FRM Terms
 */

/**
 * Data Access Object for `FRMTERMS_BOL_ItemVersion` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmterms.bol
 * @since 1.0
 */
class FRMTERMS_BOL_ItemVersionDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var FRMTERMS_BOL_ItemVersionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTERMS_BOL_ItemVersionDao
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
        return 'FRMTERMS_BOL_ItemVersion';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmterms_item_version';
    }


    /**
     * @param int $sectionId
     * @return array
     */
    public function getItems($sectionId){
        $ex = new OW_Example();
        $ex->andFieldEqual('langId', OW::getLanguage()->getInstance()->getCurrentId());
        $ex->andFieldEqual('sectionId', $sectionId);
        $ex->setOrder('`version` DESC');
        return $this->findListByExample($ex);
    }

    /**
     * @param int $version
     * @param int $sectionId
     * @return array
     */
    public function getItemsUsingVersion($version,$sectionId){
        $ex = new OW_Example();
        $ex->andFieldEqual('langId', OW::getLanguage()->getInstance()->getCurrentId());
        $ex->andFieldEqual('version', $version);
        $ex->andFieldEqual('sectionId', $sectionId);
        $ex->setOrder('`order` ASC');
        return $this->findListByExample($ex);
    }


    /**
     * @param int $version
     * @param int $sectionId
     * @return array
     */
    public function getItemsUsingMaxVersion($version,$sectionId){
        $ex = new OW_Example();
        $ex->andFieldEqual('langId', OW::getLanguage()->getInstance()->getCurrentId());
        $ex->andFieldEqual('version', $version);
        $ex->andFieldEqual('sectionId', $sectionId);
        $ex->setOrder('`order` ASC');
        return $this->findListByExample($ex);
    }

    public function getMaxVersion($sectionId)
    {
        $langId = OW::getLanguage()->getInstance()->getCurrentId();
        $query = "SELECT MAX(`version`) FROM `{$this->getTableName()}` WHERE `sectionId` = :sectionId and `langId` = :langId";
        return $this->dbo->queryForColumn($query, array('sectionId' => $sectionId, 'langId' => $langId));
    }

    /**
     * @param $sectionId
     * @param $version
     * @return int
     */
    public function deleteVersion($sectionId, $version)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('langId', OW::getLanguage()->getInstance()->getCurrentId());
        $ex->andFieldEqual('version', $version);
        $ex->andFieldEqual('sectionId', $sectionId);
        return $this->deleteByExample($ex);
    }
}