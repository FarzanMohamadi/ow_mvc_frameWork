<?php
/**
 * Data Access Object for `mailbox_last_message` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.bol
 * @since 1.0
 */
class MAILBOX_BOL_LastMessageDao extends OW_BaseDao
{

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
     * @var MAILBOX_BOL_LastMessageDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return MAILBOX_BOL_LastMessageDao
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
     * @see MAILBOX_BOL_LastMessageDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'MAILBOX_BOL_LastMessage';
    }

    /**
     * @see MAILBOX_BOL_LastMessageDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'mailbox_last_message';
    }

    /**
     * Deletes record by conversationId
     *
     * @param int $conversationId
     * @return int
     */
    public function deleteByConversationId( $conversationId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('conversationId', (int) $conversationId);
        return $this->deleteByExample($example);
    }

    /**
     * Returns record id by conversationId
     *
     * @param int $conversationId
     * @return MAILBOX_BOL_LastMessage
     */
    public function findByConversationId( $conversationId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('conversationId', (int) $conversationId);
        return $this->findObjectByExample($example);
    }
}