<?php
/**
 * Smarty truncate modifier.
 *
 * @package ow.ow_smarty.plugin
 * @since 1.0
 * @param $string
 * @param $length
 * @param bool $show_button
 * @return string
 */
function smarty_modifier_more( $string, $length, $show_button = true )
{
    if( strlen(strip_tags($string)) < $length + 50) {
        return $string;
    }
    $uniqId = FRMSecurityProvider::generateUniqueId("more-");
    $seeMoreEmbed = '<a href="javascript://" class="ow_small ow_lbutton view_more " onclick="$(\'#' . $uniqId . '\').attr(\'data-collapsed\', 0);$(this).remove();">'
        . OW::getLanguage()->text("base", "comments_see_more_label")
        . '</a>';
    $truncated = mb_substr($string, 0, $length);
    if(mb_strpos($truncated,'<')!==false) {
        $string2 = '<p>' . $string . '</p>';
        $truncated = UTIL_String::truncate_html($string2, $length);
        if( $show_button ){
            $truncated2=mb_substr($truncated, 0, -4, 'UTF-8') . ' ... '.$seeMoreEmbed . '</p>';
        }
        else{
            $truncated2=mb_substr($truncated, 0, -4, 'UTF-8') . ' ... </p>';
        }
    }
    else{
        if( $show_button ){
            $truncated2=$truncated. ' ... '.$seeMoreEmbed;
        }
        else{
            $truncated2=$truncated. ' ... ';
        }
    }
    if ( strlen($string) - strlen($truncated) < 50 )
    {
        return $string;
    }

    return '
    <span class="ow_more_text" data-collapsed="1" id="' . $uniqId . '">
        <span data-text="full">' . $string . '</span>
        <span data-text="truncated">' . $truncated2 . '</span>
    </span>';
}
