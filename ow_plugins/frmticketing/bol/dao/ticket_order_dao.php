<?php
/**
 * FRM Ticketing
 */

/**
 *Data Access Object for `frmticket_orders` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmticketing.bol
 * @since 1.0
 */
class FRMTICKETING_BOL_TicketOrderDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var FRMTICKETING_BOL_TicketOrderDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTICKETING_BOL_TicketOrderDao
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
        return 'FRMTICKETING_BOL_TicketOrder';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmticketing_orders';
    }

    public function findIsExistTitle($title)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('title', $title);
        return $this->findObjectByExample($ex);
    }

    public function softDeleteOrderById($orderId)
    {
        $sql = "UPDATE `" . $this->getTableName() . "` SET `status` = 'inactive' WHERE `id` = :orderId";
        $this->dbo->query($sql, array('orderId' => $orderId));
    }
}