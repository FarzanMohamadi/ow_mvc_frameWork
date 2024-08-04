<?php
$plugin = OW::getPluginManager()->getPlugin('blogs');

OW::getAutoloader()->addClass('Post', $plugin->getBolDir() . 'dto' . DS . 'post.php');
OW::getAutoloader()->addClass('PostDao', $plugin->getBolDir() . 'dao' . DS . 'post_dao.php');
OW::getAutoloader()->addClass('PostService', $plugin->getBolDir() . 'service' . DS . 'post_service.php');

OW::getRouter()->addRoute(new OW_Route('blogs-uninstall', 'admin/blogs/uninstall', 'BLOGS_CTRL_Admin', 'uninstall'));

OW::getRouter()->addRoute(new OW_Route('post-save-new', 'blogs/post/new', "BLOGS_CTRL_Save", 'create'));
OW::getRouter()->addRoute(new OW_Route('post-save-edit', 'blogs/post/edit/:id', "BLOGS_CTRL_Save", 'edit'));

OW::getRouter()->addRoute(new OW_Route('post', 'blogs/post/:id', "BLOGS_CTRL_View", 'index'));
OW::getRouter()->addRoute(new OW_Route('post-approve', 'blogs/post/approve/:id', "BLOGS_CTRL_View", 'approve'));

OW::getRouter()->addRoute(new OW_Route('post-part', 'blogs/post/:id/:part', "BLOGS_CTRL_View", 'index'));

OW::getRouter()->addRoute(new OW_Route('user-blog', 'blogs/user/:user', "BLOGS_CTRL_UserBlog", 'index'));

OW::getRouter()->addRoute(new OW_Route('user-post', 'blogs/:id', "BLOGS_CTRL_View", 'index'));

OW::getRouter()->addRoute(new OW_Route('blogs', 'blogs', "BLOGS_CTRL_Blog", 'index', array('list' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'latest'))));
OW::getRouter()->addRoute(new OW_Route('blogs.list', 'blogs/list/:list', "BLOGS_CTRL_Blog", 'index'));

OW::getRouter()->addRoute(new OW_Route('blog-manage-posts', 'blogs/my-published-posts', "BLOGS_CTRL_ManagementPost", 'index'));
OW::getRouter()->addRoute(new OW_Route('blog-manage-drafts', 'blogs/my-drafts', "BLOGS_CTRL_ManagementPost", 'index'));
OW::getRouter()->addRoute(new OW_Route('blog-manage-comments', 'blogs/my-incoming-comments', "BLOGS_CTRL_ManagementComment", 'index'));

OW::getRouter()->addRoute(new OW_Route('blogs-admin', 'admin/blogs', "BLOGS_CTRL_Admin", 'index'));
OW::getRouter()->addRoute(new OW_Route('back-to-plugins', 'admin/plugins', "ADMIN_CTRL_Plugins", 'index'));
OW::getRouter()->addRoute(new OW_Route('blog_delete_attachment', 'blog/deleteAttachment', 'BLOGS_CTRL_Blog', 'ajaxDeleteAttachment'));

$eventHandler = BLOGS_CLASS_EventHandler::getInstance();
$eventHandler->init();
BLOGS_CLASS_ContentProvider::getInstance()->init();

