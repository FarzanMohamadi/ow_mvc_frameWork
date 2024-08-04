<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmeconetdanesh', 'frmeconetdanesh.admin');


OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'frmeconetdanesh.tags.widget', 'frmeconetdanesh', 'main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);


$widgetService = BOL_ComponentAdminService::getInstance();
$widget = $widgetService->addWidget('FRMECONETDANESH_CMP_TagsWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'frmeconetdanesh');
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_TOP);