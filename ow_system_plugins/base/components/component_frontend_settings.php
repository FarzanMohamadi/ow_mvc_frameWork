<?php
/**
 * Widget Frontend Settings
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_ComponentFrontendSettings extends BASE_CMP_ComponentSettings
{
    public function __construct($uniqName, $componentSettings = array(), $defaultSettings = array(), $access = null) 
    {
        parent::__construct($uniqName, $componentSettings, $defaultSettings, $access);
        
        $this->markAsHidden('freeze');
    }
}

