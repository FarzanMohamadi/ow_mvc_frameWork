<?php
try {
    $widget = array();
//--
    $widget['dashboard'] = BOL_ComponentAdminService::getInstance()->addWidget('FRMFORUMPLUS_CMP_TopicGroupWidget', false);

    $placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget['dashboard'], BOL_ComponentAdminService::PLACE_DASHBOARD);

    BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);


//--
    $widget['site'] = BOL_ComponentAdminService::getInstance()->addWidget('FRMFORUMPLUS_CMP_TopicGroupWidget', false);

    $placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget['site'], BOL_ComponentAdminService::PLACE_INDEX);

    BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);
}catch(Exception $e)
{

}
