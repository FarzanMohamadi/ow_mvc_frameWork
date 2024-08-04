<?php
OW::getNavigation()->deleteMenuItem('blogs', 'main_menu_item');
OW::getNavigation()->deleteMenuItem('blogs', 'mobile_main_menu_list');

BOL_ComponentAdminService::getInstance()->deleteWidget('BLOGS_CMP_UserBlogWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('BLOGS_CMP_BlogWidget');
