<?php
/**
 * Smarty date function.
 *
 * @package ow.ow_smarty.plugin
 * @since 1.0
 */
function smarty_function_format_date( $params, $smarty )
{
    return UTIL_DateTime::formatDate($params['timestamp']);
}
?>