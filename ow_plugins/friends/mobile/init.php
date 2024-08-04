<?php
/**
 * Mobile init
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.friends.mobile
 * @since 1.6.0
 */
OW::getRouter()->addRoute(new OW_Route('friends_user_friends', 'friends/user-friends/:user', 'FRIENDS_MCTRL_List', 'index'));
OW::getRouter()->addRoute(new OW_Route('friends_user_lists_responder', 'friends/user-friends/responder/:user', 'FRIENDS_MCTRL_List', 'responder'));
FRIENDS_MCLASS_ConsoleEventHandler::getInstance()->init();

FRIENDS_CLASS_EventHandler::getInstance()->genericInit();
