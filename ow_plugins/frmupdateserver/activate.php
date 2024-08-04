<?php
/**
 * 
 * All rights reserved.
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmupdateserver', 'frmupdateserver.admin');

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'frmupdateserver.index', 'frmupdateserver', 'top_menu_item', OW_Navigation::VISIBLE_FOR_ALL);
$widget = BOL_ComponentAdminService::getInstance()->addWidget('FRMUPDATESERVER_CMP_VersionWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT, 0 );

BOL_SeoService::getInstance()->addSitemapEntity('frmupdateserver', 'frmupdateserver_sitemap', 'frmupdateserver', array(
    'frmupdateserver_download'
));