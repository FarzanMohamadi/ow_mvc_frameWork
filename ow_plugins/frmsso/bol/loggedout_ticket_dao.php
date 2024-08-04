<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsso.bol
 * @since 1.0
 */
class FRMSSO_BOL_LoggedoutTicketDao extends OW_BaseDao
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'FRMSSO_BOL_LoggedoutTicket';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmsso_loggedout_ticket';
    }

    /***
     * @param $ticket
     * @param $cookie
     * @return FRMSSO_BOL_LoggedoutTicket
     */
    public function addLoggedoutTicket($ticket)
    {
        $loggedoutTicket = new FRMSSO_BOL_LoggedoutTicket();
        $loggedoutTicket->setTicket($ticket);
        $this->save($loggedoutTicket);
        return $loggedoutTicket;
    }

    public function deleteLoggedoutTicket($ticket){
        $example = new OW_Example();
        $example->andFieldEqual('ticket', $ticket);
        return $this->deleteByExample($example);
    }
    public function getLoggedoutTicket($ticket){
        $example = new OW_Example();
        $example->andFieldEqual('ticket', $ticket);
        return $this->findObjectByExample($example);
    }

}
