<?php
/**
 * Smarty simple date modifier.
 *
 * @package ow.ow_smarty.plugin
 * @since 1.0
 */
function smarty_modifier_simple_date( $timeStamp, $dateOnly = false )
{
    return UTIL_DateTime::formatSimpleDate($timeStamp, $dateOnly);
}