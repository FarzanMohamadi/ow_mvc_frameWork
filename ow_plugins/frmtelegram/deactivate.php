<?php
/**
 * frmtelegram
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 * @since 1.0
 */

OW::getNavigation()->deleteMenuItem('frmtelegram', 'main_menu_item');
OW::getNavigation()->deleteMenuItem('frmtelegram', 'mobile_main_menu_item');

BOL_ComponentAdminService::getInstance()->deleteWidget('FRMTELEGRAM_CMP_FeedWidget');