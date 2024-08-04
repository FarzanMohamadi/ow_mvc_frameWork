<?php
/**
 * 
 * All rights reserved.
 */

$config = OW::getConfig();

if ( !$config->configExists('frmsso', 'ssoServerSecret') )
{
    $config->addConfig('frmsso', 'ssoServerSecret', '');
}
if ( !$config->configExists('frmsso', 'ssoClientSecret') )
{
    $config->addConfig('frmsso', 'ssoClientSecret', '');
}
if ( !$config->configExists('frmsso', 'ssoUrl') )
{
    $config->addConfig('frmsso', 'ssoUrl', '');
}
if ( !$config->configExists('frmsso', 'ssoSharedCookieDomain') )
{
    $config->addConfig('frmsso', 'ssoSharedCookieDomain', '');
}
if ( !$config->configExists('frmsso', 'ssoTicketValidationUrl') )
{
    $config->addConfig('frmsso', 'ssoTicketValidationUrl', '/validate-ticket/');
}
if ( !$config->configExists('frmsso', 'ssoLoginUrl') )
{
    $config->addConfig('frmsso', 'ssoLoginUrl', '/sign-in/');
}
if ( !$config->configExists('frmsso', 'ssoLogoutUrl') )
{
    $config->addConfig('frmsso', 'ssoLogoutUrl', '/sign-out/');
}
if ( !$config->configExists('frmsso', 'ssoRegistrationUrl') )
{
    $config->addConfig('frmsso', 'ssoRegistrationUrl', '/sign-up/');
}
if ( !$config->configExists('frmsso', 'ssoChangePasswordUrl') )
{
    $config->addConfig('frmsso', 'ssoChangePasswordUrl', '/change-password/');
}
if ( !$config->configExists('frmsso', 'ssoCookieKey') )
{
    $config->addConfig('frmsso', 'ssoCookieKey', 'sso-session');
}
if ( !$config->configExists('frmsso', 'ssoSameDomain') )
{
    $config->addConfig('frmsso', 'ssoSameDomain', true);
}
if ( !$config->configExists('frmsso', 'autoRegisterUsers') )
{
    $config->addConfig('frmsso', 'autoRegisterUsers', false);
}
if ( !$config->configExists('frmsso', 'usersDetailsUrl') )
{
    $config->addConfig('frmsso', 'usersDetailsUrl', '/user-details/');
}

OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmsso_loggedout_ticket`;");

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmsso_loggedout_ticket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket` (`ticket`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
