<?php
/**
 * 
 * All rights reserved.
 */


OW::getNavigation()->deleteMenuItem('frmupdateserver', 'top_menu_item');
BOL_ComponentAdminService::getInstance()->deleteWidget('FRMUPDATESERVER_CMP_VersionWidget');
