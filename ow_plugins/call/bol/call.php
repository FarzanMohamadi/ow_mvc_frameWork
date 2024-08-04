<?php
/**
 * Data Transfer Object for `frmcontactus_department` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcontactus.bol
 * @since 1.0
 */
class CALL_BOL_Call extends OW_Entity
{
    public
    $senderId,
    $mode,
    $establishTimestamp,
    $offer,
    $candidate,
    $dismissTimestamp;
}
