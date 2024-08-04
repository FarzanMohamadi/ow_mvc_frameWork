<?php
try {
    $config = OW::getConfig();
    if(!$config->configExists('frmsso', 'ssoSharedCookieDomain'))
    {
        $config->addConfig('frmsso', 'ssoSharedCookieDomain', '');
    }
}catch(Exception $e){

}