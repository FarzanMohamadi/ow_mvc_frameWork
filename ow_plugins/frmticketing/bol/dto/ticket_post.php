<?php
class FRMTICKETING_BOL_TicketPost extends OW_Entity
{
    /**
     * @var int
     */
    public $ticketId;
    /**
     * @var int
     */
    public $userId;
    /**
     * @var string
     */
    public $text;
    /**
     * @var int
     */
    public $createStamp;
}