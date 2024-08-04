<?php
/**
 * 
 * All rights reserved.
 */

$config = OW::getConfig();

if ( !$config->configExists('sso', 'ssoServerSecret') )
{
    $config->addConfig('sso', 'ssoServerSecret', '');
}
if ( !$config->configExists('sso', 'ssoClientSecret') )
{
    $config->addConfig('sso', 'ssoClientSecret', '');
}
if ( !$config->configExists('sso', 'ssoUrl') )
{
    $config->addConfig('sso', 'ssoUrl', '');
}
if ( !$config->configExists('sso', 'ssoSharedCookieDomain') )
{
    $config->addConfig('sso', 'ssoSharedCookieDomain', '');
}
if ( !$config->configExists('sso', 'ssoTicketValidationUrl') )
{
    $config->addConfig('sso', 'ssoTicketValidationUrl', '/validate-ticket/');
}
if ( !$config->configExists('sso', 'ssoLoginUrl') )
{
    $config->addConfig('sso', 'ssoLoginUrl', '');
}
if ( !$config->configExists('sso', 'ssoLogoutUrl') )
{
    $config->addConfig('sso', 'ssoLogoutUrl', '/sign-out/');
}
if ( !$config->configExists('sso', 'ssoRegistrationUrl') )
{
    $config->addConfig('sso', 'ssoRegistrationUrl', '/sign-up/');
}
if ( !$config->configExists('sso', 'ssoChangePasswordUrl') )
{
    $config->addConfig('sso', 'ssoChangePasswordUrl', '/change-password/');
}
if ( !$config->configExists('sso', 'ssoGetToken') )
{
    $config->addConfig('sso', 'ssoGetToken', '');
}
if ( !$config->configExists('sso', 'ssoSameDomain') )
{
    $config->addConfig('sso', 'ssoSameDomain', true);
}
if ( !$config->configExists('sso', 'autoRegisterUsers') )
{
    $config->addConfig('sso', 'autoRegisterUsers', false);
}
if ( !$config->configExists('sso', 'usersDetailsUrl') )
{
    $config->addConfig('sso', 'usersDetailsUrl', '');
}

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "sso_loggedout_ticket`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'sso_loggedout_ticket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket` (`ticket`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
