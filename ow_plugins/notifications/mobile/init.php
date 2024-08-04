<?php
/**
 * Mobile init
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.notifications.mobile
 * @since 1.6.0
 */

function notifications_preference_menu_item( BASE_CLASS_EventCollector $event )
{
    $router = OW_Router::getInstance();
    $language = OW::getLanguage();

    $menuItems = array();

    $menuItem = new BASE_MenuItem();

    $menuItem->setKey('email_notifications');
    $menuItem->setLabel($language->text( 'notifications', 'dashboard_menu_item'));
    $menuItem->setIconClass('ow_ic_mail');
    $menuItem->setUrl($router->urlForRoute('notifications-settings'));
    $menuItem->setOrder(3);

    $event->add($menuItem);
}

OW::getEventManager()->bind('base.preference_menu_items', 'notifications_preference_menu_item');

OW::getRouter()->addRoute(new OW_Route('notifications-settings', 'email-notifications', 'NOTIFICATIONS_MCTRL_Notifications', 'settings'));
OW::getRouter()->addRoute(new OW_Route('notifications-unsubscribe', 'email-notifications/unsubscribe/:code/:action', 'NOTIFICATIONS_MCTRL_Notifications', 'unsubscribe'));
OW::getRouter()->addRoute(new OW_Route('notifications-hide', 'notifications/hide/:id', 'NOTIFICATIONS_CTRL_Notifications', 'hideNotification'));
OW::getRouter()->addRoute(new OW_Route('notifications-notifications', 'notifications', 'NOTIFICATIONS_MCTRL_Notifications', 'notifications'));

NOTIFICATIONS_MCLASS_ConsoleEventHandler::getInstance()->init();
NOTIFICATIONS_CLASS_ConsoleBridge::getInstance()->genericInit();
NOTIFICATIONS_CLASS_EmailBridge::getInstance()->genericInit();