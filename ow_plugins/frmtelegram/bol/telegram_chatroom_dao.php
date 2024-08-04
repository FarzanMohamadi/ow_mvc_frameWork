<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 * @since 1.0
 */
class FRMTELEGRAM_BOL_TelegramChatroomDao extends OW_BaseDao
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
     * @var FRMTELEGRAM_BOL_TelegramChatroomDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return FRMTELEGRAM_BOL_TelegramChatroomDao
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
        return 'FRMTELEGRAM_BOL_TelegramChatroom';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmtelegram_chatrooms';
    }

    public function getItemByChatId($chatId){
        $ex = new OW_Example();
        $ex->andFieldEqual('chatId', $chatId);

        return $this->findListByExample($ex);
    }
    public function itemExists($chatId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('chatId', $chatId);

        return ($this->countByExample($ex)>0);
    }

    public function countChatrooms( )
    {
        $ex = new OW_Example();

        return $this->countByExample($ex);
    }

    public function findList( $onlyVisible)
    {
        $ex = new OW_Example();
        if($onlyVisible)
            $ex->andFieldEqual('visible', true);
        $ex->setOrder('`orderN` ASC');

        return $this->findListByExample($ex);
    }

}
