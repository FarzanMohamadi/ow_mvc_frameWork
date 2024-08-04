<?php
/**
 * Smarty date modifier.
 *
 * @package ow.ow_smarty.plugin
 * @since 1.0
 */
function smarty_modifier_date( $timeStamp, $dateOnly = false )
{
    return UTIL_DateTime::formatDate($timeStamp, $dateOnly);
}