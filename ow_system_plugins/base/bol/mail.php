<?php
/**
 * Data Transfer Object for `base_mail` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_Mail extends OW_Entity
{
    /**
     * @var string
     */
    public $recipientEmail;
    /**
     * @var string
     */
    public $senderEmail;
    /**
     * @var string
     */
    public $senderName;
    /**
     * @var string
     */
    public $subject;
    /**
     * @var string
     */
    public $textContent;
    /**
     * @var string
     */
    public $htmlContent;
    /**
     * @var int
     */
    public $sentTime;
    /**
     * @var int
     */
    public $priority;

    /**
     *
     * @var int
     */
    public $senderSuffix;


     /**
     *
     * @var boolean
     */
    public $sent = 0;

}
