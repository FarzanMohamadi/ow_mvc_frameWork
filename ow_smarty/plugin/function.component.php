<?php
/**
 * Smarty component function.
 *
 * @package ow.ow_smarty.plugin
 * @since 1.0
 */
function smarty_function_component( $params, $smarty )
{

    if ( !isset($params['class']) || !mb_strstr($params['class'], '_') )
    {
        throw new InvalidArgumentException('Ivalid class name provided `'.$params['class'].'`');
    }

    $class = trim($params['class']);
    unset($params['class']);

    if ( !class_exists($class) )
    {
        return '';
    }

    $cmp = OW::getClassInstance($class, $params);
    
    return $cmp->render();
}