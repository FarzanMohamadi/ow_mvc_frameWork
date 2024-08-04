<?php
/**
 * Smarty form block function.
 *
 * @package ow.ow_smarty.plugin
 * @since 1.0
 */
function smarty_block_script( $params, $script, $smarty )
{
    if ( $script === null )
    {
        return;
    }

    $document = OW::getDocument();

    if ( $document === null )
    {
        return;
    }

    $document->addOnloadScript($script);
}