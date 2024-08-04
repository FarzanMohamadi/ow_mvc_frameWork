<?php
/**
 * Widgets admin panel
 *
 * @package ow_system_plugins.admin.components
 * @since 1.0
 */
class ADMIN_CMP_DragAndDropAdminPanel extends BASE_CMP_DragAndDropPanel
{

    public function __construct( $placeName, array $componentList, $template = 'drag_and_drop_panel' )
    {
        parent::__construct($placeName, $componentList, $template);

        $customizeAllowed = BOL_ComponentAdminService::getInstance()->findPlace($placeName)->editableByUser;
        $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::BEFORE_CUSTOMIZATION_PAGE_RENDERER, array('this' => $this, 'customizeAllowed' => $customizeAllowed, 'placeName' => $placeName)));
        if(isset($event->getData()['customizeAllowed'])){
            $customizeAllowed = $event->getData()['customizeAllowed'];
        }
        $this->assign('customizeAllowed', $customizeAllowed);

        $this->assign('placeName', $placeName);
    }
    
    public function onBeforeRender()
    {
        parent::onBeforeRender();
        
        $sharedData = array(
            'additionalSettings' => $this->additionalSettingList,
            'place' => $this->placeName
        );
        
        $this->initializeJs('BASE_CTRL_AjaxComponentAdminPanel', 'OW_Components_DragAndDrop', $sharedData);
        
        $jsDragAndDropUrl = OW::getPluginManager()->getPlugin('ADMIN')->getStaticJsUrl() . 'drag_and_drop.js';
        OW::getDocument()->addScript($jsDragAndDropUrl);
    }
}