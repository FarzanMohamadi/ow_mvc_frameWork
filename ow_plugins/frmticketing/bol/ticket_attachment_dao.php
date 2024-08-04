<?php
/**
 * FRM Ticketing
 */

/**
 *Data Access Object for `ow_frmticket_attachments` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmticketing.bol
 * @since 1.0
 */
class FRMTICKETING_BOL_TicketAttachmentDao extends OW_BaseDao
{

    const TICKET_TYPE='ticket';

    const POST_TYPE='post';

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Class instance
     *
     * @var FRMTICKETING_BOL_TicketAttachmentDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return FRMTICKETING_BOL_TicketAttachmentDao
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
        return 'FRMTICKETING_BOL_TicketAttachment';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmticketing_attachments';
    }

    public function findAttachmentsByEntityIdList( $entityIds,$entityType)
    {
        $query = "
            SELECT *
            FROM `" . $this->getTableName() . "`
            WHERE `entityId` IN (" . $this->dbo->mergeInClause($entityIds) . ") AND entityType = :et
        ";

        return $this->dbo->queryForList($query,array('et'=>$entityType));
    }

    public function findAttachmentsByTypeAndId($entityType,$entityId )
    {
        $query = "
            SELECT *
            FROM `" . $this->getTableName() . "`
            WHERE `entityId` = :ei AND entityType = :et
        ";

        return $this->dbo->queryForList($query, array('ei' => $entityId,'et'=>$entityType));
    }
}