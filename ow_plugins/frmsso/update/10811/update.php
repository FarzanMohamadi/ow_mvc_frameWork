<?php
$config = OW::getConfig();
if ( !$config->configExists('frmsso', 'autoRegisterUsers') )
{
    $config->addConfig('frmsso', 'autoRegisterUsers', false);
}
if ( !$config->configExists('frmsso', 'usersDetailsUrl') )
{
    $config->addConfig('frmsso', 'usersDetailsUrl', '/user-details/');
}