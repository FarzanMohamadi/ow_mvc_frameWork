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
class FRMSSO_BOL_LoggedoutTicket extends OW_Entity
{
    public $ticket;

    public function getTicket()
    {
        return $this->ticket;
    }

    public function setTicket( $value )
    {
        $this->ticket = $value;
        return $this;
    }

}
