<?php
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'frmsecurityessentials');

$config = OW::getConfig();
if ( !$config->configExists('frmsecurityessentials', 'approveUserAfterEditProfile') )
{
    $config->addConfig('frmsecurityessentials', 'approveUserAfterEditProfile', false);
}