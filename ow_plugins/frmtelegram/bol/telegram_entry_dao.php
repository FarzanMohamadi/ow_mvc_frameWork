<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 * @since 1.0
 */
class FRMTELEGRAM_BOL_TelegramEntryDao extends OW_BaseDao
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
     * @var FRMTELEGRAM_BOL_TelegramEntryDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return FRMTELEGRAM_BOL_TelegramEntryDao
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
        return 'FRMTELEGRAM_BOL_TelegramEntry';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmtelegram_entry';
    }

    public function countEntries( $chatId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('chatId', $chatId);
        $ex->andFieldNotEqual('isDeleted', true);

        return $this->countByExample($ex);
    }

}
