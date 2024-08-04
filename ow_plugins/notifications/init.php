<?php
$plugin = OW::getPluginManager()->getPlugin('notifications');

OW::getRouter()->addRoute(new OW_Route('notifications-settings', 'email-notifications', 'NOTIFICATIONS_CTRL_Notifications', 'settings'));
OW::getRouter()->addRoute(new OW_Route('notifications-unsubscribe', 'email-notifications/unsubscribe/:code/:action', 'NOTIFICATIONS_CTRL_Notifications', 'unsubscribe'));
OW::getRouter()->addRoute(new OW_Route('notifications-hide', 'notifications/hide/:id', 'NOTIFICATIONS_CTRL_Notifications', 'hideNotification'));
OW::getRouter()->addRoute(new OW_Route('notifications-notifications', 'notifications', 'NOTIFICATIONS_CTRL_Notifications', 'notifications'));
OW::getRouter()->addRoute(new OW_Route('notifications.admin', 'notifications/admin', 'NOTIFICATIONS_CTRL_Admin', 'index'));


NOTIFICATIONS_CLASS_ConsoleBridge::getInstance()->init();
NOTIFICATIONS_CLASS_EmailBridge::getInstance()->init();

function notifications_preference_menu_item( BASE_CLASS_EventCollector $event )
{
    $router = OW_Router::getInstance();
    $language = OW::getLanguage();

    $menuItems = array();

    $menuItem = new BASE_MenuItem();

    $menuItem->setKey('email_notifications');
    $menuItem->setLabel($language->text( 'notifications', 'dashboard_menu_item'));
    $menuItem->setIconClass('ow_ic_mail ow_dynamic_color_icon');
    $menuItem->setUrl($router->urlForRoute('notifications-settings'));
    $menuItem->setOrder(3);

    $event->add($menuItem);
}

OW::getEventManager()->bind('base.preference_menu_items', 'notifications_preference_menu_item');

    
function notifications_add_console_item( BASE_CLASS_EventCollector $event )
{
    $event->add(array('label' => OW::getLanguage()->text('notifications', 'console_menu_label'), 'url' => OW_Router::getInstance()->urlForRoute('notifications-settings')));
}

OW::getEventManager()->bind('base.add_main_console_item', 'notifications_add_console_item');