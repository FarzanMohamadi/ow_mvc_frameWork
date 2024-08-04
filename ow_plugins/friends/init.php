<?php
 OW::getRouter()->addRoute(new OW_Route('friends_list', 'friends', 'FRIENDS_CTRL_List', 'index', array('list' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'friends'))));
OW::getRouter()->addRoute(new OW_Route('friends_lists', 'friends/:list', 'FRIENDS_CTRL_List', 'index'));
OW::getRouter()->addRoute(new OW_Route('friends_user_friends', 'friends/user/:user', 'FRIENDS_CTRL_List', 'index', array('list' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'user-friends'))));


if ( OW::getPluginManager()->getPlugin('friends')->getDto()->build >= 5836 )
{
    FRIENDS_CLASS_RequestEventHandler::getInstance()->init();
}

$eventHandler = FRIENDS_CLASS_EventHandler::getInstance();
$eventHandler->genericInit();

OW::getEventManager()->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME,  array($eventHandler,'onCollectProfileActionTools'));
OW::getEventManager()->bind(BASE_CMP_QuickLinksWidget::EVENT_NAME,      array($eventHandler,'onCollectQuickLinks'));
