<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 2/25/18
 * Time: 2:50 PM
 */

//OW::getPluginManager()->addPluginSettingsRouteName('frmquestions', 'questions-admin-main');

$navigation = OW::getNavigation();

$navigation->addMenuItem(
    OW_Navigation::MAIN,
    'frmquestions-index',
    'frmquestions',
    'main_menu_list',
    OW_Navigation::VISIBLE_FOR_ALL);
$navigation->addMenuItem(
    OW_Navigation::MOBILE_TOP,
    'frmquestions-index',
    'frmquestions',
    'mobile_main_menu_list',
    OW_Navigation::VISIBLE_FOR_ALL);

//$widgetService = BOL_ComponentAdminService::getInstance();
//$widget = $widgetService->addWidget('QUESTIONS_CMP_IndexWidget', false);
//$widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
