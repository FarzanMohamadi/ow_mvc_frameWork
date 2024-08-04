<?php
/**
 * Decorator block function for smarty templates. 
 *
 * @package ow.ow_smarty.plugin
 * @since 1.0
 */
function smarty_function_decorator( $params )
{
    if ( !isset($params['name']) )
    {
        throw new InvalidArgumentException('Empty decorator name!');
    }

    return OW::getThemeManager()->processDecorator($params['name'], $params);
}
