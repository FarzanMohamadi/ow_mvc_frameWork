<?php
/**
 * Data Transfer Object for `frmcontactus_department` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcontactus.bol
 * @since 1.0
 */
class CALL_BOL_CallUser extends OW_Entity
{
    public
    $userId,
    $callId,
    $role,
    $joinTimestamp,
    $leaveTimestamp;
}
