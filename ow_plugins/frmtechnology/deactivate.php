<?php
//OW::getNavigation()->deleteMenuItem('frmtechnology', 'main_menu_item');
//OW::getNavigation()->deleteMenuItem('frmtechnology', 'mobile_main_menu_item');

BOL_ComponentAdminService::getInstance()->deleteWidget('FRMTECHNOLOGY_CMP_ServicesEnterprise');
BOL_ComponentAdminService::getInstance()->deleteWidget('FRMTECHNOLOGY_CMP_ServicesUniMembers');
BOL_ComponentAdminService::getInstance()->deleteWidget('FRMTECHNOLOGY_CMP_ContactUs');