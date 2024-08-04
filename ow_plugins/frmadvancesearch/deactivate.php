<?php
/**
 * FRM Advance Search
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvancesearch
 * @since 1.0
 */

BOL_ComponentAdminService::getInstance()->deleteWidget('FRMADVANCESEARCH_MCMP_UsersSearchWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('FRMADVANCESEARCH_MCMP_FriendsSearchWidget');


OW::getNavigation()->deleteMenuItem('frmadvancesearch', 'mobile_main_menu_item');