<?php
$plugin = OW::getPluginManager()->getPlugin('blogs');

OW::getAutoloader()->addClass('Post', $plugin->getBolDir() . 'dto' . DS . 'post.php');
OW::getAutoloader()->addClass('PostDao', $plugin->getBolDir() . 'dao' . DS . 'post_dao.php');
OW::getAutoloader()->addClass('PostService', $plugin->getBolDir() . 'service' . DS . 'post_service.php');

$eventHandler = BLOGS_CLASS_EventHandler::getInstance();
$eventHandler->genericInit();

$mobileEventHandler = BLOGS_MCLASS_EventHandler::getInstance();
$mobileEventHandler->init();

BLOGS_CLASS_ContentProvider::getInstance()->init();

OW::getRouter()->addRoute(new OW_Route('blogs', 'blogs', "BLOGS_MCTRL_Blog", 'index', array('list' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'latest'))));
OW::getRouter()->addRoute(new OW_Route('blog-manage-drafts', 'blogs/my-drafts', "BLOGS_MCTRL_ManagementPost", 'index'));
OW::getRouter()->addRoute(new OW_Route('blogs.list', 'blogs/list/:list', "BLOGS_MCTRL_Blog", 'index'));
OW::getRouter()->addRoute(new OW_Route('user-post', 'blogs/:id', "BLOGS_MCTRL_View", 'index'));
OW::getRouter()->addRoute(new OW_Route('post-save-new', 'blogs/post/new', "BLOGS_MCTRL_Save", 'create'));
OW::getRouter()->addRoute(new OW_Route('post-save-edit', 'blogs/post/edit/:id', "BLOGS_MCTRL_Save", 'edit'));
OW::getRouter()->addRoute(new OW_Route('post', 'blogs/post/:id', "BLOGS_MCTRL_View", 'index'));
OW::getRouter()->addRoute(new OW_Route('blog-manage-posts', 'blogs/my-published-posts', "BLOGS_MCTRL_ManagementPost", 'index'));
OW::getRouter()->addRoute(new OW_Route('blog-manage-comments', 'blogs/my-incoming-comments', "BLOGS_MCTRL_ManagementComment", 'index'));
OW::getRouter()->addRoute(new OW_Route('blog_delete_attachment', 'blog/deleteAttachment', 'BLOGS_MCTRL_Blog', 'ajaxDeleteAttachment'));
