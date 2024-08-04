<?php
/**
 * Smarty truncate modifier.
 *
 * @package ow.ow_smarty.plugin
 * @since 1.0
 */
function smarty_modifier_truncate( $string, $length, $ending = null )
{
    if ( mb_strlen($string) <= $length )
    {
        return $string;
    }

    //find a non-character place to split
    $str = mb_substr($string, $length);
    $new_index_1 = -1;
    if(preg_match('/([^A-Za-z0-9])/', $str, $matches, PREG_OFFSET_CAPTURE)) {
        $new_index_1 = $matches[0][1];
    }
    if(preg_match('/([^A-Za-z0-9\x{0600}-\x{06FF}\x])/u', $str, $matches, PREG_OFFSET_CAPTURE)) {
        $new_index = $matches[0][1];
        if($new_index != $new_index_1){ //if persian
            $new_index = $new_index /2;
        }
        $length+=$new_index;
    }

    return UTIL_String::truncate($string, $length, $ending);
}