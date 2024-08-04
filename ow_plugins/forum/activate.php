<?php
OW::getPluginManager()->addPluginSettingsRouteName('forum', 'forum_admin_config');
OW::getPluginManager()->addUninstallRouteName('forum', 'forum_uninstall');

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'forum-default', 'forum', 'forum', OW_Navigation::VISIBLE_FOR_ALL);

$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('FORUM_CMP_ForumTopicsWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT);

$event = new BASE_CLASS_EventCollector('forum.collect_widget_places');
OW::getEventManager()->trigger($event);

foreach( $event->getData() as $widgetInfo )
{
    try
    {
        $widget = $widgetService->addWidget('FORUM_CMP_LatestTopicsWidget', false);
        $widgetPlace = $widgetService->addWidgetToPlace($widget, $widgetInfo['place']);
        $widgetService->addWidgetToPosition($widgetPlace, $widgetInfo['section'], $widgetInfo['order']);
    }
    catch ( Exception $e )
    {

    }
}

// Mobile activation
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'forum-default', 'forum', 'forum_mobile', OW_Navigation::VISIBLE_FOR_ALL);

require_once dirname(__FILE__) . DS .  'bol' . DS . 'text_search_service.php';
FORUM_BOL_TextSearchService::getInstance()->activateEntities();

// register sitemap entities
BOL_SeoService::getInstance()->addSitemapEntity('forum', 'forum_sitemap', 'forum', array(
    'forum_list',
    'forum_section',
    'forum_group',
    'forum_topic'
));
