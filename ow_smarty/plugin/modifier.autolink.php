<?php
 
/**
 * Smarty date modifier.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_smarty.plugin
 * @since 1.0
 */
function smarty_modifier_autolink( $string )
{
    return UTIL_HtmlTag::autoLink($string);
}