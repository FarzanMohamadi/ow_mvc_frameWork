<?php
OW::getPluginManager()->addPluginSettingsRouteName('blogs', 'blogs-admin');
OW::getPluginManager()->addUninstallRouteName('blogs', 'blogs-uninstall');

$widget = array();

//--
$widget['user'] = BOL_ComponentAdminService::getInstance()->addWidget('BLOGS_CMP_UserBlogWidget', false);

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget['user'], BOL_ComponentAdminService::PLACE_PROFILE);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT );

//--
$widget['site'] = BOL_ComponentAdminService::getInstance()->addWidget('BLOGS_CMP_BlogWidget', false);

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget['site'], BOL_ComponentAdminService::PLACE_INDEX);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT );

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'blogs', 'blogs', 'main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'blogs', 'blogs', 'mobile_main_menu_list', OW_Navigation::VISIBLE_FOR_MEMBER);

// register sitemap entities
BOL_SeoService::getInstance()->addSitemapEntity('blogs', 'blogs_sitemap', 'blogs', array(
    'blogs_list',
    'blogs_post_list',
    'blogs_post_authors',
    'blogs_tags',
), 'blogs_sitemap_desc');
