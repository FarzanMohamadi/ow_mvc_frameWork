<?php
/**
 * Widgets admin panel
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_DragAndDropAdminPanel extends BASE_CMP_DragAndDropPanel
{

    public function __construct( $placeName, array $componentList, $template )
    {
        parent::__construct($placeName, $componentList, $template);

        $jsDragAndDropUrl = OW::getPluginManager()->getPlugin('ADMIN')->getStaticJsUrl() . 'drag_and_drop.js';
        OW::getDocument()->addScript($jsDragAndDropUrl);

        $customizeAllowed = BOL_ComponentAdminService::getInstance()->findPlace($placeName);
        $this->assign('customizeAllowed', $customizeAllowed);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->initializeJs('BASE_CTRL_AjaxComponentAdminPanel', 'OW_Components_DragAndDrop', $this->sharedData);
    }
}