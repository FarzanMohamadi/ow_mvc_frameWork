<?php
try {
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
}catch(Exception $e){

}