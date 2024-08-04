<?php

OW::getPluginManager()->addPluginSettingsRouteName('coverphoto', 'coverphoto-admin');
try {
    $widget = BOL_ComponentAdminService::getInstance()->addWidget('COVERPHOTO_CMP_CoverPhotoWidget', false);
    $placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
    BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_TOP, 0);
} catch (Exception $ex){}

try {
    $widget =  BOL_ComponentAdminService::getInstance()->addWidget('COVERPHOTO_CMP_CoverPhotoWidget', false);
    $placeWidget =  BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, 'group');
    BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_TOP, 0);
} catch (Exception $ex){}