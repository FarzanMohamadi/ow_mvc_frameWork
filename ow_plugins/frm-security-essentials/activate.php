<?php
/**
 * User: Hamed Tahmooresi
 * Date: 12/23/2015
 * Time: 11:00 AM
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmsecurityessentials', 'frmsecurityessentials.admin');

BOL_ComponentAdminService::getInstance()->deleteWidget('BASE_CMP_ProfileWallWidget');
