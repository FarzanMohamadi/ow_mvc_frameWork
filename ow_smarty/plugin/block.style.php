<?php
/**
 * Smarty style block function.
 *
 * @package ow.ow_smarty.plugin
 * @since 1.0
 */
function smarty_block_style( $params, $styles, $smarty )
{
    if ( $styles === null )
    {
        return;
    }

    OW::getDocument()->addStyleDeclaration($styles);
}