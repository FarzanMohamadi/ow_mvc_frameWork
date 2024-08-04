<?php
BOL_LanguageService::getInstance()->addPrefix('frmgmailconnect', 'Google Connect');

//ow_base_config
$config = OW::getConfig();
if ( !$config->configExists('frmgmailconnect', 'client_key') )
{
    $config->addConfig('frmgmailconnect', 'client_key', '', 'Google Api Key');
}
if ( !$config->configExists('frmgmailconnect', 'client_id') )
{
    $config->addConfig('frmgmailconnect', 'client_id', '', 'Google Client ID');
}
if ( !$config->configExists('frmgmailconnect', 'client_secret') )
{
    $config->addConfig('frmgmailconnect', 'client_secret', '', 'Google Client Secret');
}
