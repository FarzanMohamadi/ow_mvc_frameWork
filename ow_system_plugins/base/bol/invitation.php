<?php
/**
 * Data Transfer Object for `base_invitation` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_Invitation extends OW_Entity
{
    /**
     * @var string
     */
    public $entityType;

    /**
     * @var int
     */
    public $entityId;

    /**
     * @var int
     */
    public $userId;

    /**
     *
     * @var string
     */
    public $pluginKey;

    /**
     * @var int
     */
    public $timeStamp;

    /**
     *
     * @var int
     */
    public $viewed = false;

    /**
     *
     * @var int
     */
    public $sent = false;

    /**
     *
     * @var int
     */
    public $active = true;

    /**
     *
     * @var string
     */
    public $action;

    /**
     * @var data
     */
    public $data;

    public function setData( $data )
    {
        $this->data = json_encode($data);
    }

    public function getData()
    {
        return empty($this->data) ? null : json_decode($this->data, true);
    }
}
