<?php
/**
 * Data Access Object for `base_mail` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_MailDao extends OW_BaseDao
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
     * @var BOL_MailDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_MailDao
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
        return 'BOL_Mail';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_mail';
    }

    public function findList( $count )
    {
        $example = new OW_Example();
        $example->andFieldNotEqual('sent', 1);
        $example->setOrder('priority');
        $example->setLimitClause(0, $count);

        return $this->findListByExample($example);
    }

    public function updateSentStatus( $mailId, $status = true )
    {
        if ( empty($mailId) )
        {
            return;
        }

        return $this->dbo->query(" UPDATE " . $this->getTableName() . " SET sent = ? WHERE id = ? ", array( $status, $mailId) );
    }

    public function deleteSentMails()
    {
        $example = new OW_Example();
        $example->andFieldEqual('sent', 1);
        $this->deleteByExample($example);
    }
    
    public function deleteByRecipientEmail( $email )
    {
        $example = new OW_Example();
        $example->andFieldEqual('recipientEmail', $email);
        
        $this->deleteByExample($example);
    }

    public function saveList( array $list )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $list);
    }

    /**
     * @param BOL_Mail $entity
     * @return bool|OW_Entity
     */
    public function save($entity) {
        if (BOL_MailService::getInstance()->isMailSystematic($entity->recipientEmail)) {
            return false;
        }

        return parent::save($entity);
    }

}