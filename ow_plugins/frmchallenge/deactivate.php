<?php
/**
 * FRM Challenge
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmchallenge
 * @since 1.0
 */

OW::getNavigation()->deleteMenuItem('frmchallenge', 'main_menu_item');
OW::getNavigation()->deleteMenuItem('frmchallenge', 'mobile_main_menu_item');
BOL_ComponentAdminService::getInstance()->deleteWidget('FRMCHALLENGE_CMP_ChallengeWidget');
