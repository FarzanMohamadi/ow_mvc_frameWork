<?php
OW::getNavigation()->deleteMenuItem('groups', 'main_menu_list');
OW::getNavigation()->deleteMenuItem('groups', 'mobile_main_menu_list');
BOL_ComponentAdminService::getInstance()->deleteWidget('GROUPS_CMP_UserGroupsWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('GROUPS_CMP_GroupsWidget');
