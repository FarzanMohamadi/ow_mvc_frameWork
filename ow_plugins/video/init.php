<?php
$plugin = OW::getPluginManager()->getPlugin('video');

$classesToAutoload = array(
    'VideoProviders' => $plugin->getRootDir() . 'classes' . DS . 'video_providers.php'
);

OW::getAutoloader()->addClassArray($classesToAutoload);

OW::getRouter()->addRoute(
    new OW_Route('video_view_list', 'video/viewlist/:listType', 'VIDEO_CTRL_Video', 'viewList',
        array('listType' => array('default' => 'latest'))
    )
);

OW::getRouter()->addRoute(new OW_Route('video_list_index', 'video', 'VIDEO_CTRL_Video', 'viewList'));
OW::getRouter()->addRoute(new OW_Route('view_clip', 'video/view/:id', 'VIDEO_CTRL_Video', 'view'));
OW::getRouter()->addRoute(new OW_Route('edit_clip', 'video/edit/:id', 'VIDEO_CTRL_Video', 'edit'));
OW::getRouter()->addRoute(new OW_Route('view_list', 'video/viewlist/:listType', 'VIDEO_CTRL_Video', 'viewList'));
OW::getRouter()->addRoute(new OW_Route('view_tagged_list_st', 'video/viewlist/tagged', 'VIDEO_CTRL_Video', 'viewTaggedList'));
OW::getRouter()->addRoute(new OW_Route('view_tagged_list', 'video/viewlist/tagged/:tag', 'VIDEO_CTRL_Video', 'viewTaggedList'));
OW::getRouter()->addRoute(new OW_Route('video_user_video_list', 'video/user-video/:user', 'VIDEO_CTRL_Video', 'viewUserVideoList'));
OW::getRouter()->addRoute(new OW_Route('video_validate_iframe', 'video/validate_iframe', 'VIDEO_CTRL_Video', 'validateVideoIframe'));
OW::getRouter()->addRoute(new OW_Route('video_admin_config', 'admin/video', 'VIDEO_CTRL_Admin', 'index'));

OW::getThemeManager()->addDecorator('video_list_item', $plugin->getKey());

VIDEO_CLASS_EventHandler::getInstance()->init();
VIDEO_CLASS_ContentProvider::getInstance()->init();