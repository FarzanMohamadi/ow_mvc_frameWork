<?php
$updateDir = dirname(__FILE__) . DS;
Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'frmforumplus');

OW::getPluginManager()->addPluginSettingsRouteName('frmforumplus','frmforumplus_admin_config');