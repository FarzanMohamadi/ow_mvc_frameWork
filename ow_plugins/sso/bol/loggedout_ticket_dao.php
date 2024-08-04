<?php
/**
 * 
 * All rights reserved.
 */
/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.sso.bol
 * @since 1.0
 */

class SSO_BOL_LoggedoutTicketDao extends OW_BaseDao
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
        return 'SSO_BOL_LoggedoutTicket';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'sso_loggedout_ticket';
    }

    /***
     * @param $ticket
     * @param $cookie
     * @return SSO_BOL_LoggedoutTicket
     */
    public function addLoggedoutTicket($ticket)
    {
        $loggedoutTicket = new SSO_BOL_LoggedoutTicket();
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
