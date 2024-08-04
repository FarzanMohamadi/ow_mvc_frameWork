<?php
Updater::getLanguageService()->importPrefixFromDir(__DIR__ . DS . 'langs');
try
{
    $widget = BOL_ComponentAdminService::getInstance()->addWidget('FRMUPDATESERVER_CMP_VersionWidget', false);
    $placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
    BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT, 0 );
}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry(json_encode($e));
}
