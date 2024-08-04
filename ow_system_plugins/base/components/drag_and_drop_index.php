<?php
/**
 * Widgets index panel
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_DragAndDropIndex extends BASE_CMP_DragAndDropFrontendPanel
{
    public function __construct( $placeName, array $componentList, $customizeMode, $componentTemplate )
    {
        parent::__construct($placeName, $componentList, $customizeMode, $componentTemplate);
    }

    public function setSidebarPosition( $value )
    {
        $this->assign('sidebarPosition', $value);
    }
}