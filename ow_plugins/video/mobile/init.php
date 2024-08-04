<?php
$plugin = OW::getPluginManager()->getPlugin('video');

$classesToAutoload = array(
    'VideoProviders' => $plugin->getRootDir() . 'classes' . DS . 'video_providers.php'
);

OW::getAutoloader()->addClassArray($classesToAutoload);

OW::getRouter()->addRoute(
    new OW_Route( 'video_view_list', 'video/viewlist/:listType', 'VIDEO_MCTRL_Video', 'viewList',
        array('listType' => array('default' => 'latest'))
    )
);

OW::getRouter()->addRoute(new OW_Route('video_list_index', 'video', 'VIDEO_MCTRL_Video', 'viewList'));

OW::getRouter()->addRoute(new OW_Route('view_clip', 'video/view/:id', 'VIDEO_MCTRL_Video', 'view'));
OW::getRouter()->addRoute(new OW_Route('view_list', 'video/viewlist/:listType', 'VIDEO_MCTRL_Video', 'viewList'));
OW::getRouter()->addRoute(new OW_Route('edit_clip', 'video/edit/:id', 'VIDEO_MCTRL_Video', 'edit'));
OW::getRouter()->addRoute(new OW_Route('view_tagged_list_st', 'video/viewlist/tagged', 'VIDEO_MCTRL_Video', 'viewTaggedList'));
OW::getRouter()->addRoute(new OW_Route('view_tagged_list', 'video/viewlist/tagged/:tag', 'VIDEO_MCTRL_Video', 'viewTaggedList'));
OW::getRouter()->addRoute(new OW_Route('video_user_video_list', 'video/user-video/:user', 'VIDEO_MCTRL_Video', 'viewUserVideoList'));
OW::getThemeManager()->addDecorator('mobile_video_list_item', $plugin->getKey());
VIDEO_MCLASS_EventHandler::getInstance()->init();