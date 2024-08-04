<?php
/**
 * Created by PhpStorm.
 * User: Ali Khatami
 * Date: 11/26/2018
 * Time: 3:54 PM
 */


$path = OW::getPluginManager()->getPlugin('frmjcse')->getRootDir() . 'langs.zip';
Updater::getLanguageService()->importPrefixFromZip($path, 'frmjcse');

$widget = BOL_ComponentAdminService::getInstance()->addWidget('FRMJCSE_CMP_SearchWidget', false);

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT );

