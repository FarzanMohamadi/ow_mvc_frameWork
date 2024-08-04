<?php
OW::getNavigation()->deleteMenuItem('forum', 'forum');

BOL_ComponentAdminService::getInstance()->deleteWidget('FORUM_CMP_ForumTopicsWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('FORUM_CMP_LatestTopicsWidget');

// Mobile deactivation
OW::getNavigation()->deleteMenuItem('forum', 'forum_mobile');
FORUM_BOL_TextSearchService::getInstance()->deactivateEntities();
