<?php
/**
 * Smarty print var function.
 *
 * @package ow.ow_smarty.plugin
 * @since 1.0
 */
function smarty_function_print_var( $params, $smarty )
{
    $isEcho = ((isset($params['echo'])) && $params['echo'] === true);
    printVar($params['var'], $isEcho);
}
?>