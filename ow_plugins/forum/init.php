<?php
$plugin = OW::getPluginManager()->getPlugin('forum');

OW::getAutoloader()->addClass('ForumSelectBox', $plugin->getRootDir() . 'classes' . DS . 'forum_select_box.php');
OW::getAutoloader()->addClass('ForumStringValidator', $plugin->getRootDir() . 'classes' . DS . 'forum_string_validator.php');

OW::getRouter()->addRoute(new OW_Route('forum-default', 'forum', 'FORUM_CTRL_Index', 'index'));
OW::getRouter()->addRoute(new OW_Route('customize-default', 'forum/customize', 'FORUM_CTRL_Customize', 'index'));
OW::getRouter()->addRoute(new OW_Route('section-default', 'forum/section/:sectionId', 'FORUM_CTRL_Section', 'index'));
OW::getRouter()->addRoute(new OW_Route('group-default', 'forum/:groupId', 'FORUM_CTRL_Group', 'index'));
OW::getRouter()->addRoute(new OW_Route('topic-default', 'forum/topic/:topicId', 'FORUM_CTRL_Topic', 'index'));

OW::getRouter()->addRoute(new OW_Route('add-topic-default', 'forum/addTopic', 'FORUM_CTRL_AddTopic', 'index'));
OW::getRouter()->addRoute(new OW_Route('add-topic', 'forum/addTopic/:groupId', 'FORUM_CTRL_AddTopic', 'index'));

OW::getRouter()->addRoute(new OW_Route('sticky-topic', 'forum/stickyTopic/:topicId/:page', 'FORUM_CTRL_Topic', 'stickyTopic'));
OW::getRouter()->addRoute(new OW_Route('lock-topic', 'forum/lockTopic/:topicId/:page', 'FORUM_CTRL_Topic', 'lockTopic'));
OW::getRouter()->addRoute(new OW_Route('set-as-topic-conclusion-post', 'forum/setTopicConclusionPost/:topicId/:postId', 'FORUM_CTRL_Topic', 'setTopicConclusionPost'));
OW::getRouter()->addRoute(new OW_Route('delete-topic', 'forum/deleteTopic/:topicId', 'FORUM_CTRL_Topic', 'deleteTopic'));
OW::getRouter()->addRoute(new OW_Route('get-post', 'forum/getPost/:postId', 'FORUM_CTRL_Topic', 'getPost'));
OW::getRouter()->addRoute(new OW_Route('edit-post', 'forum/edit-post/:id', 'FORUM_CTRL_EditPost', 'index'));
OW::getRouter()->addRoute(new OW_Route('edit-topic', 'forum/edit-topic/:id', 'FORUM_CTRL_EditTopic', 'index'));
OW::getRouter()->addRoute(new OW_Route('move-topic', 'forum/moveTopic', 'FORUM_CTRL_Topic', 'moveTopic'));
OW::getRouter()->addRoute(new OW_Route('subscribe-topic', 'forum/subscribe-topic/:id', 'FORUM_CTRL_Topic', 'subscribeTopic'));
OW::getRouter()->addRoute(new OW_Route('unsubscribe-topic', 'forum/unsubscribe-topic/:id', 'FORUM_CTRL_Topic', 'unsubscribeTopic'));

OW::getRouter()->addRoute(new OW_Route('add-post', 'forum/addPost/:topicId/:uid', 'FORUM_CTRL_Topic', 'addPost'));
OW::getRouter()->addRoute(new OW_Route('delete-post', 'forum/deletePost/:topicId/:postId', 'FORUM_CTRL_Topic', 'deletePost'));
OW::getRouter()->addRoute(new OW_Route('forum_delete_attachment', 'forum/deleteAttachment', 'FORUM_CTRL_Topic', 'ajaxDeleteAttachment'));
OW::getRouter()->addRoute(new OW_Route('forum_admin_config', 'admin/plugins/forum', 'FORUM_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('forum_uninstall', 'admin/forum/uninstall', 'FORUM_CTRL_Admin', 'uninstall'));
OW::getRouter()->addRoute(new OW_Route('forum_advanced_search', 'forum/advanced-search', 'FORUM_CTRL_Search', 'advanced'));
OW::getRouter()->addRoute(new OW_Route('forum_advanced_search_result', 'forum/advanced-search/result', 'FORUM_CTRL_Search', 'advancedResult'));
OW::getRouter()->addRoute(new OW_Route('forum_search', 'forum/search', 'FORUM_CTRL_Search', 'inForums'));
OW::getRouter()->addRoute(new OW_Route('forum_search_group', 'forum/:groupId/search', 'FORUM_CTRL_Search', 'inGroup'));
OW::getRouter()->addRoute(new OW_Route('forum_search_section', 'forum/section/:sectionId/search', 'FORUM_CTRL_Search', 'inSection'));
OW::getRouter()->addRoute(new OW_Route('forum_search_topic', 'forum/topic/:topicId/search', 'FORUM_CTRL_Search', 'inTopic'));
OW::getRouter()->addRoute(new OW_Route('forum_approve_topic', 'forum/approve/:id', 'FORUM_CTRL_Topic', 'approve'));

FORUM_CLASS_EventHandler::getInstance()->init();
FORUM_CLASS_ContentProvider::getInstance()->init();