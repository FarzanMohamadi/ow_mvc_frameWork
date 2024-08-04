<?php
/**
 * Decorator block function for smarty templates. 
 *
 * @package ow.ow_smarty.plugin
 * @since 1.0
 */
function smarty_block_block_decorator( $params, $content )
{
    if ( !isset($params['name']) )
    {
        throw new InvalidArgumentException('Empty decorator name!');
    }

    if ( $content === null )
    {
        return;
    }

    return OW::getThemeManager()->processBlockDecorator($params['name'], $params, $content);
}