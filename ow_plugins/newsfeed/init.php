<?php
$plugin = OW::getPluginManager()->getPlugin("newsfeed");

OW::getRouter()->addRoute(new OW_Route('newsfeed_admin_settings', 'admin/plugins/newsfeed', 'NEWSFEED_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('newsfeed_admin_customization', 'admin/plugins/newsfeed/customization', 'NEWSFEED_CTRL_Admin', 'customization'));

OW::getRouter()->addRoute(new OW_Route('newsfeed_view_item', 'newsfeed/:actionId', 'NEWSFEED_CTRL_Feed', 'viewItem'));

$eventHandler = NEWSFEED_CLASS_EventHandler::getInstance();
$eventHandler->genericInit();

OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_PLUGIN_DEACTIVATE, array($eventHandler, 'onPluginDeactivate'));
OW::getEventManager()->bind(OW_EventManager::ON_AFTER_PLUGIN_ACTIVATE, array($eventHandler, 'onPluginActivate'));
OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($eventHandler, 'onPluginUninstall'));
OW::getEventManager()->bind('feed.on_item_render', array($eventHandler, 'desktopItemRender'));
OW::getEventManager()->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($eventHandler, 'onCollectProfileActions'));
OW::getEventManager()->bind('feed.on_item_render', array($eventHandler, 'feedItemRenderFlagBtn'));

// Formats
NEWSFEED_CLASS_FormatManager::getInstance()->init();

/* Built-in Formats */
NEWSFEED_CLASS_FormatManager::getInstance()->addFormat("text", "NEWSFEED_FORMAT_Text");
NEWSFEED_CLASS_FormatManager::getInstance()->addFormat("image", "NEWSFEED_FORMAT_Image");
NEWSFEED_CLASS_FormatManager::getInstance()->addFormat("image_list", "NEWSFEED_FORMAT_ImageList");
NEWSFEED_CLASS_FormatManager::getInstance()->addFormat("image_content", "NEWSFEED_FORMAT_ImageContent");
NEWSFEED_CLASS_FormatManager::getInstance()->addFormat("content", "NEWSFEED_FORMAT_Content");
NEWSFEED_CLASS_FormatManager::getInstance()->addFormat("video", "NEWSFEED_FORMAT_Video");