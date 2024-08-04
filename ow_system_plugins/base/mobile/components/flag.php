<?php
/**
 * Singleton. 'Flag' Data Access Object
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_MCMP_Flag extends BASE_CMP_Flag
{

    public function __construct( $entityType, $entityId )
    {
        parent::__construct($entityType, $entityId);
        
        $this->setTemplate(OW::getPluginManager()->getPlugin("base")->getMobileCmpViewDir() . "flag.html");
    }
}