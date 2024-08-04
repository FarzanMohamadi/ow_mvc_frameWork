<?php
/**
 * Smarty modifier to render hashtaga/mentions/emojies.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_smarty.plugin
 * @since 1.0
 * @param $string
 * @return string
 */
function smarty_modifier_prettify( $string )
{
    $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $string)));
    if (isset($stringRenderer->getData()['string'])) {
        $string = ($stringRenderer->getData()['string']);
    }
    return $string;
}
