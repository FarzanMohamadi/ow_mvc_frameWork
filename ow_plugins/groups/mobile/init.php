<?php
OW::getRouter()->addRoute(new OW_Route('groups-view', 'groups/:groupId', 'GROUPS_MCTRL_Groups', 'view'));

GROUPS_CLASS_EventHandler::getInstance()->genericInit();
$eventHandler = GROUPS_MCLASS_EventHandler::getInstance();
$eventHandler->genericInit();

OW::getEventManager()->bind('mobile.invitations.on_item_render', array($eventHandler, 'onInvitationsItemRender'));
OW::getEventManager()->bind('feed.on_item_render', array($eventHandler, "onFeedItemRenderDisableActions"));



OW::getEventManager()->bind('feed.collect_widgets', array(GROUPS_CLASS_EventHandler::getInstance(), "onFeedCollectWidgets"));
OW::getEventManager()->bind('feed.on_widget_construct', array(GROUPS_CLASS_EventHandler::getInstance(), "onFeedWidgetConstruct"));
OW::getEventManager()->bind('feed.on_item_render', array($eventHandler, "onFeedItemRender"));
OW::getEventManager()->bind('newsfeed.update_status.form', array($eventHandler, 'newsfeedUpdateStatusFrom'));

//Frontend Routs
OW::getRouter()->addRoute(new OW_Route('groups-create', 'groups/create', 'GROUPS_MCTRL_Groups', 'create'));
OW::getRouter()->addRoute(new OW_Route('groups-edit', 'groups/:groupId/edit', 'GROUPS_MCTRL_Groups', 'edit'));
OW::getRouter()->addRoute(new OW_Route('groups-join', 'groups/:groupId/join', 'GROUPS_MCTRL_Groups', 'join'));
OW::getRouter()->addRoute(new OW_Route('groups-customize', 'groups/:groupId/customize', 'GROUPS_MCTRL_Groups', 'customize'));
OW::getRouter()->addRoute(new OW_Route('groups-most-popular', 'groups/most-popular', 'GROUPS_MCTRL_Groups', 'mostPopularList'));
OW::getRouter()->addRoute(new OW_Route('groups-latest', 'groups/latest', 'GROUPS_MCTRL_Groups', 'latestList'));
OW::getRouter()->addRoute(new OW_Route('groups-invite-list', 'groups/invitations', 'GROUPS_MCTRL_Groups', 'inviteList'));
OW::getRouter()->addRoute(new OW_Route('groups-my-list', 'groups/my', 'GROUPS_MCTRL_Groups', 'myGroupList'));

OW::getRouter()->addRoute(new OW_Route('groups-index', 'groups', 'GROUPS_MCTRL_Groups', 'index'));
OW::getRouter()->addRoute(new OW_Route('groups-user-groups', 'users/:user/groups', 'GROUPS_MCTRL_Groups', 'userGroupList'));
OW::getRouter()->addRoute(new OW_Route('groups-leave', 'groups/:groupId/leave', 'GROUPS_MCTRL_Groups', 'leave'));

OW::getRouter()->addRoute(new OW_Route('groups-user-list', 'groups/:groupId/users', 'GROUPS_MCTRL_Groups', 'userList'));
//OW::getRouter()->addRoute(new OW_Route('groups-private-group', 'groups/:groupId/private', 'GROUPS_MCTRL_Groups', 'privateGroup'));

OW::getRegistry()->addToArray(BASE_CMP_AddNewContent::REGISTRY_DATA_KEY,
    array(
        BASE_CMP_AddNewContent::DATA_KEY_ICON_CLASS => 'ow_ic_comment',
        BASE_CMP_AddNewContent::DATA_KEY_URL => OW::getRouter()->urlForRoute('groups-create'),
        BASE_CMP_AddNewContent::DATA_KEY_LABEL => OW::getLanguage()->text('groups', 'add_new_label')
    ));

GROUPS_CLASS_ConsoleBridge::getInstance()->init();
GROUPS_CLASS_ContentProvider::getInstance()->init();