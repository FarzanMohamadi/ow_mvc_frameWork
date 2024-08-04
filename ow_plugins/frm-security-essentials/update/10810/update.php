<?php
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'frmsecurityessentials');

$config = OW::getConfig();

if ( !$config->configExists('frmsecurityessentials', 'disabled_home_page_action_types') )
{
    $config->addConfig('frmsecurityessentials', 'disabled_home_page_action_types', '');
}