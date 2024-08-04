<?php
BOL_ComponentAdminService::getInstance()->deleteWidget('NEWSFEED_CMP_MyFeedWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('NEWSFEED_CMP_EntityFeedWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('NEWSFEED_CMP_SiteFeedWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('NEWSFEED_CMP_UserFeedWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('NEWSFEED_MCMP_MyFeedWidget');

// Mobile deactivation
OW::getNavigation()->deleteMenuItem('newsfeed', 'newsfeed_feed');
