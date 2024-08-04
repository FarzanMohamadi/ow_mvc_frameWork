<?php
/**
 * Widgets admin page
 *
 * @package ow_system_plugins.admin.components
 * @since 1.0
 */
class ADMIN_CMP_DashboardWidgetPage extends BASE_CMP_DragAndDropFrontendPanel
{
    public function __construct( $placeName, array $componentList, $customizeMode, $componentTemplate = null )
    {
        if ( empty($componentTemplate) )
        {
            $componentTemplate = $customizeMode ? 'drag_and_drop_page_customize' : 'drag_and_drop_page';
        }
        
        parent::__construct($placeName, $componentList, $customizeMode, $componentTemplate);
    }
}