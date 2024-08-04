<?php
/**
 * 
 * @package ow.ow_smarty.plugin
 * @since 1.0
 */
function smarty_function_add_content($params, $smarty)
{ 
    if ( empty($params['key']) )
    {
        return '';
    }
    
    $eventKey = $params['key'];
    unset($params['key']);
     
    $event = new BASE_CLASS_EventCollector($eventKey, $params);
    OW::getEventManager()->trigger($event);
    
    $data = $event->getData();
    
    if ( empty($data) )
    {
        return '';
    }
    
    return implode('', $data);
}
