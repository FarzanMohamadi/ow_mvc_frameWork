<?php
$updateDir = dirname(__FILE__) . DS;

try
{
    Updater::getWidgetService()->deleteWidgetPlace('dashboard-GROUPS_CMP_UserGroupsWidget');
}
catch( Exception $e ) {}

Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'groups');
