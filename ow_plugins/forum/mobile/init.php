<?php
$plugin = OW::getPluginManager()->getPlugin('forum');

OW::getAutoloader()->addClass('ForumSelectBox', $plugin->getRootDir() . 'classes' . DS . 'forum_select_box.php');
OW::getAutoloader()->addClass('ForumStringValidator', $plugin->getRootDir() . 'classes' . DS . 'forum_string_validator.php');

OW::getRouter()->addRoute(new OW_Route('forum-default', 'forum', 'FORUM_MCTRL_Forum', 'index'));
OW::getRouter()->addRoute(new OW_Route('section-default', 'forum/section/:sectionId', 'FORUM_MCTRL_Section', 'index'));
OW::getRouter()->addRoute(new OW_Route('topic-default', 'forum/topic/:topicId', 'FORUM_MCTRL_Topic', 'index'));
OW::getRouter()->addRoute(new OW_Route('group-default', 'forum/:groupId', 'FORUM_MCTRL_Group', 'index'));
OW::getRouter()->addRoute(new OW_Route('forum_search', 'forum/search', 'FORUM_MCTRL_Search', 'inForums'));
OW::getRouter()->addRoute(new OW_Route('forum_search_group', 'forum/:groupId/search', 'FORUM_MCTRL_Search', 'inGroup'));
OW::getRouter()->addRoute(new OW_Route('forum_search_section', 'forum/section/:sectionId/search', 'FORUM_MCTRL_Search', 'inSection'));
OW::getRouter()->addRoute(new OW_Route('forum_search_topic', 'forum/topic/:topicId/search', 'FORUM_MCTRL_Search', 'inTopic'));
OW::getRouter()->addRoute(new OW_Route('add-topic', 'forum/addTopic/:groupId', 'FORUM_MCTRL_AddTopic', 'index'));
OW::getRouter()->addRoute(new OW_Route('add-post', 'forum/addPost/:topicId', 'FORUM_MCTRL_AddPost', 'index'));
OW::getRouter()->addRoute(new OW_Route('lock-topic', 'forum/ajaxLockTopic/:topicId', 'FORUM_MCTRL_Topic', 'ajaxLockTopic'));
OW::getRouter()->addRoute(new OW_Route('sticky-topic', 'forum/ajaxStickyTopic/:topicId', 'FORUM_MCTRL_Topic', 'ajaxStickyTopic'));
OW::getRouter()->addRoute(new OW_Route('subscribe-topic', 'forum/ajaxSubscribeTopic/:topicId', 'FORUM_MCTRL_Topic', 'ajaxSubscribeTopic'));
OW::getRouter()->addRoute(new OW_Route('delete-topic', 'forum/ajaxDeleteTopic/:topicId', 'FORUM_MCTRL_Topic', 'ajaxDeleteTopic'));
OW::getRouter()->addRoute(new OW_Route('forum_delete_attachment', 'forum/deleteAttachment', 'FORUM_MCTRL_Topic', 'ajaxDeleteAttachment'));
OW::getRouter()->addRoute(new OW_Route('edit-topic', 'forum/edit-topic/:id', 'FORUM_MCTRL_EditTopic', 'index'));
OW::getRouter()->addRoute(new OW_Route('delete-post', 'forum/deletePost/:topicId/:postId', 'FORUM_MCTRL_Topic', 'ajaxDeletePost'));
OW::getRouter()->addRoute(new OW_Route('edit-post', 'forum/edit-post/:id', 'FORUM_MCTRL_EditPost', 'index'));
OW::getRouter()->addRoute(new OW_Route('forum_advanced_search', 'forum/advanced-search', 'FORUM_MCTRL_Search', 'advanced'));
OW::getRouter()->addRoute(new OW_Route('forum_advanced_search_result', 'forum/advanced-search/result', 'FORUM_MCTRL_Search', 'advancedResult'));
OW::getRouter()->addRoute(new OW_Route('move-topic', 'forum/moveTopic', 'FORUM_CTRL_Topic', 'moveTopic'));
OW::getRouter()->addRoute(new OW_Route('set-as-topic-conclusion-post', 'forum/setTopicConclusionPost/:topicId/:postId', 'FORUM_MCTRL_Topic', 'setTopicConclusionPost'));
FORUM_MCLASS_EventHandler::getInstance()->init();
FORUM_CLASS_ContentProvider::getInstance()->init();