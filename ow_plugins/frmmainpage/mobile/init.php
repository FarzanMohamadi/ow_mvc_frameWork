<?php
/**
 * frmmainpage
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmslideshow
 * @since 1.0
 */
OW::getRouter()->addRoute(new OW_Route('frmmainpage.index', 'main/index', 'FRMMAINPAGE_MCTRL_Index', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.dashboard', 'main/dashboard', 'FRMMAINPAGE_MCTRL_Index', 'dashboard'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.user.groups', 'main/user-groups', 'FRMMAINPAGE_MCTRL_Index', 'userGroups'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.friends', 'main/friends', 'FRMMAINPAGE_MCTRL_Index', 'friends'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.mailbox', 'main/mailbox', 'FRMMAINPAGE_MCTRL_Index', 'mailbox'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.mailbox.type', 'main/mailbox/:type', 'FRMMAINPAGE_MCTRL_Index', 'mailbox'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.settings', 'main/settings', 'FRMMAINPAGE_MCTRL_Index', 'settings'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.notifications', 'main/notifications', 'FRMMAINPAGE_MCTRL_Index', 'notifications'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.photos', 'main/photos', 'FRMMAINPAGE_MCTRL_Index', 'photos'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.videos', 'main/videos', 'FRMMAINPAGE_MCTRL_Index', 'videos'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.chatGroups', 'main/chats-groups', 'FRMMAINPAGE_MCTRL_Index', 'chatsAndGroups'));

OW::getRouter()->addRoute(new OW_Route('frmmainpage.distinctChatChanelGroup', 'main/distinct-chat-chanel-groups/:list', 'FRMMAINPAGE_MCTRL_Index', 'distinctChatChanelGroup'));

OW::getRouter()->addRoute(new OW_Route('frmmainpage.old.index', 'frmmainpage/index', 'FRMMAINPAGE_MCTRL_Index', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.old.dashboard', 'frmmainpage/dashboard', 'FRMMAINPAGE_MCTRL_Index', 'dashboard'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.old.user.groups', 'frmmainpage/user-groups', 'FRMMAINPAGE_MCTRL_Index', 'userGroups'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.old.friends', 'frmmainpage/friends', 'FRMMAINPAGE_MCTRL_Index', 'friends'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.old.mailbox', 'frmmainpage/mailbox', 'FRMMAINPAGE_MCTRL_Index', 'mailbox'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.old.mailbox.type', 'frmmainpage/mailbox/:type', 'FRMMAINPAGE_MCTRL_Index', 'mailbox'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.old.settings', 'frmmainpage/settings', 'FRMMAINPAGE_MCTRL_Index', 'settings'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.old.notifications', 'frmmainpage/notifications', 'FRMMAINPAGE_MCTRL_Index', 'notifications'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.old.photos', 'frmmainpage/photos', 'FRMMAINPAGE_MCTRL_Index', 'photos'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.old.videos', 'frmmainpage/videos', 'FRMMAINPAGE_MCTRL_Index', 'videos'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.old.chatGroups', 'frmmainpage/chats-groups', 'FRMMAINPAGE_MCTRL_Index', 'chatsAndGroups'));

OW::getRouter()->addRoute(new OW_Route('frmmainpage.mailbox_responder', 'frmmainpage/mailbox/responder', 'FRMMAINPAGE_MCTRL_Index', 'mailbox_responder'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.friends_responder', 'frmmainpage/friends/responder', 'FRMMAINPAGE_MCTRL_Index', 'friends_responder'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.user.groups_responder', 'frmmainpage/user-groups/responder', 'FRMMAINPAGE_MCTRL_Index', 'userGroups_responder'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.photos_responder', 'frmmainpage/photos/responder', 'FRMMAINPAGE_MCTRL_Index', 'photos_responder'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.videos_responder', 'frmmainpage/videos/responder', 'FRMMAINPAGE_MCTRL_Index', 'videos_responder'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.chatGroups_responder', 'frmmainpage/chats-groups/responder', 'FRMMAINPAGE_MCTRL_Index', 'chatGroups_responder'));

$eventHandler = new FRMMAINPAGE_MCLASS_EventHandler();
$eventHandler->init();
